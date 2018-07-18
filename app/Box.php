<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Box extends Model
{

    public function _tipologia(){
        return $this->belongsTo('\App\BoxTipology', 'tipologia');
    }

    public function _posizione(){
        return $this->belongsTo('\App\BoxPosition', 'posizione');
    }

    public function _contents(){
        return $this->belongsToMany('\App\Content', 'box_content_maps');
    }




//    public function _consultabileDaiGruppi() {
//
//    }



}
