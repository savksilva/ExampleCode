<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\bankAcountRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusOrder;

class OrderController extends Controller
{
    public function createOrder(bankAcountRequest $request)
    {
       
        //dd($request->all());
        $request->validate();
        DB::beginTransaction();
        try {
            $status_order = DB::table('list_values')->where('code','pendiente')->pluck('id_list_value');
            $benefeciario = DB::table('user_bank_acounts')->insertGetId([
                'name_lname' => $request->beneficiario['name_lname'],
                'document_type' => $request->beneficiario['tipo_documento'],
                'number_document' => $request->beneficiario['cedula'],
                'email' => $request->beneficiario['email_beneficiario'],
                'bank_id' => $request->beneficiario['bank'],
                'type_account' => $request->beneficiario['type_account'],
                'is_frecuent' => $request->beneficiario['user_frecuent'],
                'number_of_account' => $request->beneficiario['number_account'],
                'user_id' => $request->user['id_user'],
                "created_at" =>  date('Y-m-d H:i:s'),
                "updated_at" =>  date('Y-m-d H:i:s')
            ]);
            $order = DB::table('orders')->insertGetId([
                'method_pay' => $request->method_pay,
                'usdAmount' => $request->usd,
                'bsfAmount' => $request->bsf,
                'rate_offered' => $request->rate_offered,
                'value_list_id' => $status_order[0],
                'user_id' => $request->user['id_user'],
                'user_bank_acount_id' => $benefeciario,
                "created_at" =>  date('Y-m-d H:i:s'),
                "updated_at" =>  date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            echo $e;
            return "Ha ocurrido un error";
        }
        DB::commit();
        $email_status = (object) array("subject"=>"[Cambio de estatus] Orden nro.".$this->number_pad($order,5)." - Pendiente comprobante",
        "name"=>$request->user["name"],
        "email"=>$request->user["email"],
        "status"=>"Por enviar comprobante",
        "mensaje"=>"Tu Orden Nro. ".$this->number_pad($order,5)." por la cantidad de: $".$request->usd. " ha sido generada exitosamente.");
        Mail::to($request->user["email"])->send(new StatusOrder($email_status));
        return "Su orden fue enviada. Revisa tu correo para ver las instrucciones de transferencia";
        
    }
    public static function searchOrder(){
        DB::beginTransaction();
        try{
            $orders = DB::table('orders')
                ->where("user_id",Auth::user()->id_user)
                ->where('value_list_id', '<>', 6)
                ->join("list_values","orders.value_list_id","=","list_values.id_list_value")
                ->get();
        }catch(\Exception $e){
            DB::rollback();
        }
        DB::commit();
        return $orders;
    }
    function number_pad($number,$n) {
        return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
    }
}
