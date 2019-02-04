<?php

namespace App\Http\Controllers;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Requests\StoreUploadVoucher;
use App\Http\Requests\UpdateProfileRules;
use App\Mail\StatusOrder;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $notification = NotificationController::searchNotification()->sortByDesc('created_at')->values()->all();
        $orders = OrderController::searchOrder()->sortByDesc('created_at')->values()->all();
        foreach ($orders as $key => $order) {

            $data = $this->number_pad($order->id_order, 5);
            $orders[$key]->id_order = $data;

        }

        $client = $this->httpGet('https://s3.amazonaws.com/dolartoday/data.json');
        $client_dollar = json_decode($client);

        $price_old = DB::table('price_dollars')->first();
        if ($client_dollar != null) {
            if ($client_dollar->USD->dolartoday != $price_old->price) {
                DB::table('price_dollars')
                    ->where('id_price_dollar', $price_old->id_price_dollar)
                    ->update([
                        'price' => $client_dollar->USD->dolartoday,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s'),
                    ]);
                $price_old->price = $client_dollar->USD->dolartoday;
            }
        }

        $banks = DB::table('banks')->get();
        if (Auth::check()) {
            $user = Auth::user();
            $frecuent_account_bank = DB::table('user_bank_acounts')
                ->where('user_id', $user->id_user)
                ->where('is_frecuent', 1)
                ->distinct()
                ->get();
            $frecuent = $this->unique_multidim_array($frecuent_account_bank, "number_of_account");
            return view('client.home', [
                "notification" => $notification, "orders" => $orders,
                "price_dolar" => ($price_old->price + $price_old->addPrice),
                "banks" => $banks,
                "frecuent_bank" => $frecuent,
            ]);
        }
        return view('client.home', [
            "notification" => $notification,
            "orders" => $orders,
            "price_dolar" => ($price_old->price + $price_old->addPrice),
            "banks" => $banks,
            "frecuent_bank" => null,
        ]);
    }

    public function httpGet($url)
    {

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;

    }
    public function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val->$key, $key_array)) {
                $key_array[$i] = $val->$key;
                $temp_array[$i] = $val;
                $i++;
            }
        }
        return $temp_array;
    }
    public function profile()
    {
        $user = DB::table('users')
            ->leftjoin("countries", "users.country_id", "=", "countries.id_country")
            ->leftjoin("states", "users.city_id", "=", "states.id_state")
            ->leftjoin("user_verifications", "users.id_user", "=", "user_verifications.user_id")
            ->where("users.id_user", Auth::user()->id_user)
            ->get(
                ["countries.name as country",
                    "countries.phonecode",
                    "users.name", "users.lname",
                    "users.date_birthday",
                    "user_verifications.*",
                    "states.name as state",
                ]);
        //dd($user); //Uncomment to know what the database brings
        return view('client.profile', [
            "user" => $user[0]
        ]);
    }

    public function number_pad($number, $n)
    {
        return str_pad((int) $number, $n, "0", STR_PAD_LEFT);
    }

    public function edit()
    {
        $user = DB::table('users')
            ->leftjoin("countries", "users.country_id", "=", "countries.id_country")
            ->leftjoin("states", "users.city_id", "=", "states.id_state")
            ->leftjoin("user_verifications", "users.id_user", "=", "user_verifications.user_id")
            ->where("users.id_user", Auth::user()->id_user)
            ->get(
                ["countries.name as country",
                    "countries.phonecode",
                    "users.name", "users.lname",
                    "users.date_birthday",
                    "users.country_id",
                    "user_verifications.*",
                    "states.name as state",
                    "states.id_state",
                ]);
        $country = DB::table('countries')->get();
        $state = DB::table('states')
            ->where("country_id", $user[0]->country_id)->get();
        //dd($user); //Uncomment to know what the database brings
        return view('client.edit', [
            "user" => $user[0],
            "countries" => $country,
            "states" => $state
        ]);
    }

    public function state(Request $req)
    {
        $state = DB::table('states')
            ->where("country_id", $req->id)->get();
        return Json_encode($state);
    }

    public function editProfile(UpdateProfileRules $req)
    {
        $req->validate();
        $verifications = DB::table('user_verifications')->where('user_id', Auth::user()->id_user)->get();
        // dd($verifications);  //Uncomment to know what the database brings
        DB::beginTransaction();
        try {
            DB::table('users')
                ->where('id_user', Auth::user()->id_user)
                ->update([
                    'name' => $req->name,
                    'lname' => $req->lname,
                    'date_birthday' => $req->date_birthday,
                    'country_id' => $req->country,
                    'city_id' => $req->states,
                ]);
            if ($verifications->isEmpty()) {

                $verif = DB::table('user_verifications')->insert([
                    'phone' => $req->phone,
                    'image_document' => null,
                    'email_verifed' => 0,
                    'user_id' => Auth::user()->id_user,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                ]);
            } else {
                DB::table('user_verifications')
                    ->where('user_id', Auth::user()->id_user)
                    ->update([
                        'phone' => $req->phone,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        } catch (Exception $e) {
            return $e;
            DB::rollBack();
        }
        DB::commit();

        return redirect('/Client/profile');

    }

    public function verificationDash()
    {
        return view('client.verification');
    }

    /** Upload Voucher */
    public function uploadVoucherPost(StoreUploadVoucher $request, $order_id)
    {
        $file = $request->file('fileToUpload');
        $filename = 'voucher-' . Auth::user()->id_user . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/vouchers', $filename);
        DB::beginTransaction();
        try {

            DB::table('orders')
                ->where('id_order', $order_id)
                ->update([
                    'proof_received' => "storage/vouchers/" . $filename,
                    'value_list_id' => DB::table('list_values')->where('code', 'por_verificar')->value('id_list_value'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $user = DB::table('orders')
                ->join("users", "users.id_user", "=", "orders.user_id")
                ->where('id_order', $order_id)
                ->get();

            $email_status = (object) array("subject" => "[Cambio de estatus] Orden nro. " . $this->number_pad($user[0]->id_order, 5) . " - Por verificar",
                "name" => $user[0]->name,
                "email" => $user[0]->email,
                "status" => "Por confirmar comprobante",
                "mensaje" => "Hola cómo estás? Ahora que enviaste el comprobante de la transferencia realizada comenzaremos el proceso de verificación. Una vez verificado el documento anexado en nuestra plataforma, nos pondremos en contacto #qontigo para informarte el estatus de tu envío.");
            
                Mail::to($user[0]->email)->send(new StatusOrder($email_status));
            $status_process = DB::table('list_values')->where('code', 'por_verificar')->pluck('id_list_value');

        } catch (Exception $e) {
            return json_encode([
                "error" => 'error al subir el archivo',
                'append' => true
            ]);
            DB::rollBack();
        }
        DB::commit();

        echo json_encode([
            "status" => "success",
            "path" => $path
        ]);
    }
}
