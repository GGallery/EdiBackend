<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;
use DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function contents(Request $request)
    {

        /* PARAMETRI DI RICERCA */

        $filter['tipologia'] =                      $request->input('tipologia');
        $filter['tipologia_compare_type'] =         $request->input('tipologia_compare_type', '=');

        $filter['categoria'] =                      $request->input('categoria');
        $filter['categoria_compare_type'] =         $request->input('categoria_compare_type', '=');

        $filter['sottocategoria'] =                 $request->input('sottocategoria');
        $filter['sottocategoria_compare_type'] =    $request->input('sottocategoria_compare_type', '=');

        $filter['livello'] =                        $request->input('livello');
        $filter['livello_compare_type'] =           $request->input('livello_compare_type', '=');

        $filter['formato'] =                        $request->input('formato');
        $filter['formato_compare_type'] =           $request->input('formato_compare_type', '=');

        $filter['prodotto'] =                       $request->input('prodotto');
        $filter['prodotto_compare_type'] =          $request->input('prodotto_compare_type', '=');

        $filter['offset'] =                         $request->input('offset', 0);
        $filter['limit'] =                          $request->input('limit', 30);

        $filter['orderkey'] =                       $request->input('orderkey', 'id');
        $filter['orderdir'] =                       $request->input('orderdir', 'asc');


        //        TESTO LIBERO RICERCA
        $filter['text'] =                           $request->input('text');

        /* FINE PARAMETRI DI RICERCA */


        $q = \App\Content::with('categoria', '_ratingAvg');
        $q = \App\Content::whereRaw('1 != 0');

        $q = $q->select('*');
        $q = $q->where('pubblicato',  1);

        if($filter['livello'])
            $q = $q->where('livello', $filter['livello_compare_type'], $filter['livello']);

        if($filter['categoria'])
            $q = $q->whereHas('categoria', function($sq) use($filter) {
                $sq->where('parent_id', $filter['categoria']);
            });

        if($filter['text']){
            $var = '(MATCH (titolo) AGAINST (\''.$filter['text'].'\') *4 
            MATCH (alias) AGAINST (\''.$filter['text'].'\') *3 + 
            MATCH (descrizione) AGAINST (\''.$filter['text'].'\') *2 +
            MATCH (abstract) AGAINST (\''.$filter['text'].'\')) as rank ';

            $q = $q->selectRaw($var);

            $q = $q->whereRaw('MATCH(titolo, abstract, descrizione, alias) AGAINST(\''.$filter['text'].'\')');
        }

        $counttotal = $q->count();

        $q = $q->limit($filter['limit'])->offset($filter['offset']);
        $q = $q->orderBy($filter['orderkey'], $filter['orderdir']);


        $res['data'] = $q->get();
        $res['query'] = $q->toSql();
        $res['count'] = $counttotal;
        $res['filter'] = $filter;

        return json_encode($res);

    }

    public function content(Request $request, $id)
    {
        $data = \App\Content::with('_categoria', '_livello' , '_tipologia', '_formato', '_prodotto', '_ratingAvg')->find($id);

        return json_encode($data);
    }

    public function rating(Request $request, $id){

        $data = DB::table('rating')
            ->select('rating')
            ->where('id_contenuto', $id)
            ->groupBy('rating')
            ->get();


        return json_encode($data);
    }

    public function subcategories(Request $request, $id = null){

        $data = $id
            ? \App\Category::where('parent_id', $id)->get()
            : \App\Category::where('parent_id', '<>', 0)->get()
        ;

        return json_encode($data);
    }

    public function categories(Request $request, $id = null){
        $data = $id
            ? \App\Category::find($id)->get()
            : \App\Category::all()
        ;

        return json_encode($data);
    }

    public function boxes(Request $request){

        $filter['posizione'] = $request->input('posizione');
        $filter['tipologia'] = $request->input('tipologia');

        $q = \App\Box::where('pubblicato',  1);

        if($filter['posizione'])
            $q = $q->where('posizione',  $filter['posizione']);

        if($filter['tipologia'])
            $q = $q->where('tipologia',  $filter['tipologia']);

        $res['data'] = $q->get();
        $res['query'] = $q->toSql();
        $res['count'] = $res['data']->count();
        $res['filter'] = $filter;

        return json_encode($res);
    }

    public function box(Request $request){

        $box_id =                   $request['box_id'];
        $current_category_id =      $request['category_id'];
        $current_content_id  =      $request['content_id'];
        $current_user_id =          1;
        $current_user_usergroup =   [1,2,3,4,5];

        $box = \App\Box::with('_tipologia')->find($box_id);

        // TODO: da rendere condizionale
        $relatedContents = $box->_contents()->allRelatedIds()->implode(',');

        $query = $box->_tipologia->query;
        $statements= ['whereRaw', 'join', 'selectRaw', 'limit', 'havingRaw', 'orderByRaw'];

        $tmp = $query; //inutile

        $q = DB::table('contents');
        $q = $q->select('contents.*')->distinct();
        $q = $q->limit($box->max_elementi);

//        $q = $q->join('content_roles_maps', 'contents.id', '=', 'content_roles_maps.content_id');
//        $q = $q->whereIn('role_id', $current_user_usergroup );


        // ELABORAZIONE QUERY

        if($query) {
            foreach ($statements as $statement) {
                $s = $this->grepQuery($query, $statement);
                foreach ($s as $item) {

                    $item = str_replace('{{current_category_id}}', $current_category_id, $item);
                    $item = str_replace('{{current_content_id}}', $current_content_id, $item);
                    $item = str_replace('{{current_user_id}}', $current_user_id, $item);
                    $item = str_replace('{{current_user_usergroup}}', implode(",", $current_user_usergroup), $item);
                    $item = str_replace('{{current_box_id}}', $box_id, $item);
                    $item = str_replace('{{current_box_related_contents}}', $relatedContents, $item);

                    $q = $q->$statement(DB::raw($item));
                }
            }
        }


        // CONDIZIONI SPECIFICHE
        switch ($box->tipologia) {

            // Chi ha visto questo ha visto anche
            case 1:
                $next_content_ids = $this->getNextContent($current_content_id, $current_user_usergroup);
                $q = $q->whereIn('contents.id',$next_content_ids);

                break;

            // Selezione statica dei contenuti
            case 2:
                break;

            // Contenuti della stessa categorie
            case 3:
                break;

            // Ultimi Contenuti
            case 5:
                break;

        }


        // QUERY LOG
        $log = $q->toSql();

        // TOTAL ROW
        //$total = $q->total();

        // PRINT CONTENUTI
        //$contents = 'QUERY NOT RUNNING';
        $contents =$q->get();

        // ARRAY DI RETURN

        $data['log'] = $log;
        $data['box'] = $box;
        //$data['total'] = $total;
        $data['contents'] = $contents;
        //$data['tmp']=$next_content_ids;

        return json_encode($data);


    }


    private function grepQuery($text, $tag){

        $delimiter = '#';
        $startTag = '{'.$tag.'}';
        $endTag = '{/'.$tag.'}';
        $regex = $delimiter . preg_quote($startTag, $delimiter)
            . '(.*?)'
            . preg_quote($endTag, $delimiter)
            . $delimiter
            . 's';
        preg_match_all($regex,$text,$matches);

        return $matches ? $matches[1] : [];

    }

    private function getNextContent($content_id, $current_user_usergroup){

        $data = \App\Log::select('next_content_id as correlato', DB::raw('count(next_content_id) as totale'))

            ->join('content_roles_maps', 'next_content_id', '=', 'content_roles_maps.content_id')
            ->whereIn('role_id', $current_user_usergroup )

            ->where('logs.content_id', $content_id)
            ->where('next_content_id', '<>',$content_id)
            ->groupBy('next_content_id')
            ->orderBy('totale',  'desc')
            ->limit(4)
            ->get();


        $data2 = \App\Log::select('logs.content_id as correlato', DB::raw('count(logs.content_id) as totale'))

            ->join('content_roles_maps', 'logs.content_id', '=', 'content_roles_maps.content_id')
            ->whereIn('role_id', $current_user_usergroup )

            ->where('next_content_id', $content_id)
            ->where('logs.content_id', '<>',$content_id)
            ->groupBy('logs.content_id')
            ->orderBy('totale',  'desc')
            ->limit(4)
            ->get();


        $collet1 = collect($data);
        $collet2 = collect($data2);

        $merge = $collet1->merge($collet2);
        $merge = $merge->sortByDesc('totale');
        $merge = $merge->unique('correlato');
        $merge = $merge->pluck('correlato');
        $merge = $merge->values()->all();

        return $merge;

    }
}
