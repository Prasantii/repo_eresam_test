<?php

namespace App\Transformers;

use App\Http\Models\WajibRetribusi;
use App\Http\Models\DetailImage;
use App\Http\Models\AppVersion;
use App\Http\Models\Tagihan;
use App\Http\Models\Petugas;
use League\Fractal\TransformerAbstract;

use DB;

class WajibRetribusiTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(WajibRetribusi $warr)
    {

        $app = AppVersion::where('appid','1')->first();

        $data = DB::table('wajib_retribusi as a')
                ->select('a.*','b.name as namedistricts','c.name as namevillages','d.nama as namajenis','d.luas as luasjenis','d.tarif_kota','d.tarif_gampong')
                ->leftJoin('districts as b','a.district_id','=','b.id')
                ->leftJoin('villages as c','a.villages_id','=','c.id')
                ->leftJoin('jenis_retribusi as d','a.jenis_id','=','d.id')
                ->where('a.id',$warr->id)
                ->first();

        if($data->kota == 1){
            $je = $data->namajenis.'- Luas:'.$data->luasjenis;
            $tarf = number_format($data->tarif_kota);
        }elseif($data->gampong == 1){
            $je = $data->namajenis.'- Luas:'.$data->luasjenis;
            $tarf = number_format($data->tarif_gampong);
        }else{
            $je = '-';
            $tarf = '-';
        }

        

        $zona = DB::table('zona as a')
                    ->select('a.*','b.id_districts','c.district_id','c.villages_id')
                    ->leftJoin('detail_zona as b','a.id','=','b.id_zona')
                    ->leftJoin('wajib_retribusi as c','b.id_districts','=','c.district_id')
                    ->where('c.villages_id',$data->villages_id)
                    ->first();

        $photo_rumah = DetailImage::where('id_wr',$data->id)->first();
        $tagihan = Tagihan::where('id_wr',$data->id)->get();
        
        
        $lokasitgas = DB::table('lokasi_tugas')
                        ->select('id_petugas')
                        ->where('villages_id',$data->villages_id)
                        ->first();
         
                        
        if(empty($lokasitgas)){
            $namapett = '';
            $hppett = '';
        }else{
            $ambilpetugas = Petugas::where('id',$lokasitgas->id_petugas)->first();
            $namapett = $ambilpetugas->nama;
            $hppett = $ambilpetugas->hp;
        }
        
        

        
        return [
            'wajib_retribusi' => array(
                'success'               => true,
                'id'           => $data->id,
                'nik'                   => $data->nik,
                'nama'                  => $data->nama,
                'hp'                    => $data->hp,
                'alamat'                => $data->alamat.' - KEC.'.$data->namedistricts.' - GAP.'.$data->namevillages,
                'username'              => $data->username,
                'email'                 => $data->email,
                'photo'                 => $data->photo,
                'ktp'                 => $data->ktp,
                'jenis_retribusi'       => $je,
                'jenis_retribusi_tarif' => $tarf,
                'code'                  => $data->code,
                'qrcode'                => $data->qrcode,
                'zona'                  => $zona->nama,
                'lat'                => $data->lat,
                'lng'                => $data->lng,
                'is_active'             => $data->is_active,
                'token'                 => $data->token,
                'token_expiry'          => $data->token_expiry,
                'registered'            => $data->created_at,
                'email_verify'          => $data->email_verify,
                'app_id'                => $app->appid,
                'app_version'           => $app->versi,
                'photo_rumah'           => $photo_rumah,
                'penanggung_jawab'  => $namapett,
                'no_petugas'  => $hppett,             
                // 'tagihan'           => $tagihan,
                )
        ];
    }
}
