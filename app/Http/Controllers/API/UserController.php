<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    public function index(){
        return User::all();
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users',
            'phone'=>'required|unique:users,phone|digits_between:9,20',
            'password'=>'required|string|confirmed|min:8',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password)
        ]);

        return response()->json([
            'msg'=>'User Inserted Successfully',
            'user'=>$user
        ]);
    }

    //login api method call
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=> 'required|string|email',
            'password'=>'required|string|min:8'
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }
        if(!$token = auth()->attempt($validator->validated()))
        {
            return response()->json(['success'=>false,'msg'=>'Username & Password is incorrect']);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL()*60
        ]);
    }

    //logout api method
    public function logout()
    {
        try{
            auth()->logout();
            return response()->json(['success'=>true,'msg'=>'User logged out!']);
        }catch(\Exception $e){
            return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
        }
    }

    //profile method
    public function profile()
    {
        try{
            return response()->json(['success'=>true,'data'=>auth()->user()]);
        }catch(\Exception $e){
            return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
        }
    }

    //update profile method
    public function updateProfile(Request $request)
    {
        if(auth()->user())
        {
            $validator = Validator::make($request->all(),[
                'id'=>'required',
                'first_name'=>'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string|email',
            ]);
            if($validator->fails())
            {
                return response()->json($validator->errors());
            }
            $user = User::find($request->id);
            $user->first_name =$request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->save();
            return response()->json(['success'=>true,'msg'=>'User Date','data'=>$user]);
        }
        else{
            return response()->json(['success'=>false,'msg'=>'User is not Authenticated.']);
        }
    }

    public function sendVerifyMail($email){
        if(auth()->user()){
            $user = User::where('email',$email)->get();
            if(count($user) > 0){

                $random = Str::random(40);
                $domain = URL::to('/');
                $url = $domain.'/verify-mail/'.$random;

                $data['url'] = $url;
                $data['email'] = $email;
                $data['title'] = "Email Verification";
                $data['body'] = "Please click here to below to verify your mail.";

                Mail::send('verifyMail',['data'=>$data],function($message) use ($data){
                    $message->to($data['email'])->subject($data['title']);
                });

                $user = User::find($user[0]['id']);
                $user->remember_token = $random;
                $user->save();

                return response()->json(['success'=>true,'msg'=>'Mail sent successfully.']);

            }
            else{
                return response()->json(['success'=>false,'msg'=>'User is not found!']);
            }
        }
        else{
            return response()->json(['success'=>false,'msg'=>'User is not Authenticated.']);
        }
    }
    public function verificationMail($token)
    {
        $user = User::where('remember_token',$token)->get();
        if(count($user) > 0){
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            $user = User::find($user[0]['id']);
            $user->remember_token = '';
            $user->status=1;
            $user->email_verified_at = $datetime;
            $user->save();

            return "<h1>Email verified successfully.</h1>";
        }
        else{
            return view('404');
        }
    }
}
