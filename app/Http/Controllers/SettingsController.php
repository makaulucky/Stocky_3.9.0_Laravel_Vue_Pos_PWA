<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Server;
use App\Models\Setting;
use App\Models\PosSetting;
use App\Models\Client;
use App\Models\Warehouse;
use File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class SettingsController extends Controller
{


    //-------------- Update  Settings ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Setting::class);

        $setting = Setting::findOrFail($id);
        $currentAvatar = $setting->logo;
        if ($request->logo != $currentAvatar) {

            $image = $request->file('logo');
            $path = public_path() . '/images';
            $filename = rand(11111111, 99999999) . $image->getClientOriginalName();

            $image_resize = Image::make($image->getRealPath());
            $image_resize->resize(80, 80);
            $image_resize->save(public_path('/images/' . $filename));

            $userPhoto = $path . '/' . $currentAvatar;
            if (file_exists($userPhoto)) {
                if ($setting->logo != 'logo-default.png') {
                    @unlink($userPhoto);
                }
            }
        } else {
            $filename = $currentAvatar;
        }
        if ($request['currency'] != 'null') {
            $currency = $request['currency'];
        } else {
            $currency = null;
        }

        if ($request['client'] != 'null') {
            $client = $request['client'];
        } else {
            $client = null;
        }

        if ($request['warehouse'] != 'null') {
            $warehouse = $request['warehouse'];
        } else {
            $warehouse = null;
        }

        if ($request['default_language'] != 'null') {
            $default_language = $request['default_language'];
        } else {
            $default_language = 'en';
        }
        Setting::whereId($id)->update([
            'currency_id' => $currency,
            'client_id' => $client,
            'warehouse_id' => $warehouse,
            'email' => $request['email'],
            'default_language' =>  $default_language,
            'CompanyName' => $request['CompanyName'],
            'CompanyPhone' => $request['CompanyPhone'],
            'CompanyAdress' => $request['CompanyAdress'],
            'footer' => $request['footer'],
            'developed_by' => $request['developed_by'],
            'logo' => $filename,
        ]);

        return response()->json(['success' => true]);
    }

      //-------------- Update  Payment Gateway ---------------\\

      public function Update_payment_gateway(Request $request)
      {
          $this->authorizeForUser($request->user('api'), 'update', Setting::class);

          if($request['deleted'] == 'true'){
            $this->setEnvironmentValue([
                'STRIPE_KEY' => '',
                'STRIPE_SECRET' => '',
            ]);

        }else{
            $this->setEnvironmentValue([
                'STRIPE_KEY' => $request['stripe_key'] !== null?'"' . $request['stripe_key'] . '"':'',
                'STRIPE_SECRET' => $request['stripe_secret'] !== null?'"' . $request['stripe_secret'] . '"':'"' . env('STRIPE_SECRET') . '"',
            ]);
        }

            Artisan::call('config:cache');
            Artisan::call('config:clear');

        return response()->json(['success' => true]);

      }

       //-------------- Update  sms_config ---------------\\

       public function sms_config(Request $request)
       {
           $this->authorizeForUser($request->user('api'), 'update', Setting::class);
 
           
             $this->setEnvironmentValue([
                 'TWILIO_SID' => $request['TWILIO_SID'] !== null?'"' . $request['TWILIO_SID'] . '"':'"' . env('TWILIO_SID') . '"',
                 'TWILIO_TOKEN' => $request['TWILIO_TOKEN'] !== null?'"' . $request['TWILIO_TOKEN'] . '"':'"' . env('TWILIO_TOKEN') . '"',
                 'TWILIO_FROM' => $request['TWILIO_FROM'] !== null?'"' . $request['TWILIO_FROM'] . '"':'"' . env('TWILIO_FROM') . '"',
             ]);
 
             Artisan::call('config:cache');
             Artisan::call('config:clear');
 
         return response()->json(['success' => true]);
 
       }

    //-------------- Get_sms_config ---------------\\

    public function get_sms_config(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Setting::class);
        Artisan::call('config:cache');
        Artisan::call('config:clear');

        $item['TWILIO_SID'] = env('TWILIO_SID');
        $item['TWILIO_FROM'] = env('TWILIO_FROM');
        $item['TWILIO_TOKEN'] = '';
        $item['gateway'] = 'Twilio';

        return response()->json(['sms' => $item], 200);
    }
 

    //-------------- Get Payment Gateway ---------------\\

    public function Get_payment_gateway(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Setting::class);
        Artisan::call('config:cache');
        Artisan::call('config:clear');

        $item['stripe_key'] = env('STRIPE_KEY');
        $item['stripe_secret'] = '';
        $item['deleted'] = false;

        return response()->json(['gateway' => $item], 200);
    }

    
  


    //-------------- Update  SMTP ---------------\\

    public function updateSMTP(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Server::class);

        Server::whereId($id)->update([
            'host' => $request['host'],
            'port' => $request['port'],
            'username' => $request['username'],
            'password' => $request['password'],
            'encryption' => $request['encryption'],
        ]);

        return response()->json(['success' => true]);

    }


     //-------------- Update Pos settings ---------------\\

     public function update_pos_settings(Request $request, $id)
     {
        $this->authorizeForUser($request->user('api'), 'update', Setting::class);
 
        request()->validate([
            'note_customer' => 'required',
        ]);

        PosSetting::whereId($id)->update([
             'note_customer'  => $request['note_customer'],
             'show_note'      => $request['show_note'],
             'show_barcode'   => $request['show_barcode'],
             'show_discount'  => $request['show_discount'],
             'show_customer'  => $request['show_customer'],
             'show_email'     => $request['show_email'],
             'show_phone'     => $request['show_phone'],
             'show_address'   => $request['show_address'],
         ]);
 
         return response()->json(['success' => true]);
 
     }


     //-------------- Get Pos Settings ---------------\\

     public function get_pos_Settings(Request $request)
     {
         $this->authorizeForUser($request->user('api'), 'view', Setting::class);
 
         $PosSetting = PosSetting::where('deleted_at', '=', null)->first();

         return response()->json([
             'pos_settings' => $PosSetting
            ], 200);
    
    }

    //-------------- Get All Settings ---------------\\

    public function getSettings(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Setting::class);

        $settings = Setting::where('deleted_at', '=', null)->first();
        if ($settings) {
            if ($settings->currency_id) {
                if (Currency::where('id', $settings->currency_id)->where('deleted_at', '=', null)->first()) {
                    $item['currency_id'] = $settings->currency_id;
                } else {
                    $item['currency_id'] = '';
                }
            } else {
                $item['currency_id'] = '';
            }

            if ($settings->client_id) {
                if (Client::where('id', $settings->client_id)->where('deleted_at', '=', null)->first()) {
                    $item['client_id'] = $settings->client_id;
                } else {
                    $item['client_id'] = '';
                }
            } else {
                $item['client_id'] = '';
            }

            if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->where('deleted_at', '=', null)->first()) {
                    $item['warehouse_id'] = $settings->warehouse_id;
                } else {
                    $item['warehouse_id'] = '';
                }
            } else {
                $item['warehouse_id'] = '';
            }

            $item['id'] = $settings->id;
            $item['email'] = $settings->email;
            $item['CompanyName'] = $settings->CompanyName;
            $item['CompanyPhone'] = $settings->CompanyPhone;
            $item['CompanyAdress'] = $settings->CompanyAdress;
            $item['logo'] = $settings->logo;
            $item['footer'] = $settings->footer;
            $item['developed_by'] = $settings->developed_by;
            $item['default_language'] = $settings->default_language;

            $Currencies = Currency::where('deleted_at', null)->get(['id', 'name']);
            $clients = client::where('deleted_at', '=', null)->get(['id', 'name']);
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);

            return response()->json([
                'settings' => $item ,
                'currencies' => $Currencies,
                'clients' => $clients , 
                'warehouses' => $warehouses
            ], 200);
        } else {
            return response()->json(['statut' => 'error'], 500);
        }
    }

    //-------------- Get STMP ---------------\\

    public function getSMTP(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Server::class);

        $server = Server::where('deleted_at', '=', null)->first();

        if ($server) {
            return response()->json(['server' => $server], 200);
        } else {
            return response()->json(['statut' => 'error'], 500);
        }
    }


    //-------------- Clear_Cache ---------------\\

    public function Clear_Cache(Request $request)
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
    }

   
    //-------------- Set Environment Value ---------------\\

    public function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $str .= "\r\n";
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
    
                $keyPosition = strpos($str, "$envKey=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
    
                if (is_bool($keyPosition) && $keyPosition === false) {
                    // variable doesnot exist
                    $str .= "$envKey=$envValue";
                    $str .= "\r\n";
                } else {
                    // variable exist                    
                    $str = str_replace($oldLine, "$envKey=$envValue", $str);
                }            
            }
        }
    
        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }
    
        app()->loadEnvironmentFrom($envFile);    
    
        return true;
    }

}
