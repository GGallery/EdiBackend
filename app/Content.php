<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Content extends Model
{

    public function _categoria(){
        return $this->belongsTo('\App\Category', 'categoria' );
    }

    public function _livello(){
        return $this->belongsTo('\App\ContentLevel', 'livello' );
    }

    public function _tipologia(){
        return $this->belongsTo('\App\ContentTipology', 'tipologia' );
    }

    public function _formato(){
        return $this->belongsToMany('\App\ContentFormat', 'content_formats_maps'  );
    }

    public function _prodotto(){
        return $this->belongsToMany('\App\ContentProduct', 'content_products_maps'  );
    }

    public function _visibileDaiGruppi(){
        return $this->belongsToMany('TCG\\Voyager\\Models\\Role', 'content_roles_maps', 'id');
    }

    public function _ratingList(){
        return $this->hasMany('\App\ContentRate');
    }

    public function _ratingAvg(){
        return $this->belongsTo( '\App\ContentRateAvg', 'rating');
    }

}
