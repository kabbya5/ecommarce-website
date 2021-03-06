<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserRoleController extends Controller
{
    public function allUsers(){
        $users = User::latest()->paginate(15);
        return view('admin.user_role.index',compact('users'));
    }

    public function userView($id){
        $user = User::find($id);
        $LastOrder = Order::where('user_id',$id)->latest()->first();
        if($LastOrder){
            $userLastOrder= Carbon::createFromFormat('Y-m-d H:i:s', $LastOrder['created_at'])->format('M d Y');
        }else{
            $userLastOrder = 'Null';
        }
        
        $userOrder = Order::where('user_id',$id)->get();
        $totalAmount = $userOrder->sum('subtotal');

        // seller product information
        $totalProduct = Product::where('seller_id',$id)->get();
        $ProductCreate = Product::where('seller_id',$id)->latest()->first();
        if($ProductCreate){
            $lastProductCreate= Carbon::createFromFormat('Y-m-d H:i:s', $ProductCreate['updated_at'])->format('M d Y');
        }else{
            $lastProductCreate = "Null"; 
        }
        
        return view('admin.user_role.view',compact(
            'user',
            'userLastOrder',
            'userOrder',
            'totalAmount',
            'totalProduct',
            'lastProductCreate',

        ));
    }
    public function userProducts($id){
        $user = User::find($id);
        $products = Product::join('brands','brands.id','products.brand_id')
            ->join('categories','categories.id','products.category_id')
            ->select('products.*','brands.brand_name','categories.name')
            ->where('seller_id',$id)->orderBy('view','desc')->paginate(15);
        return view('admin.user_role.user_product', compact(
            'user',
            'products'
        ));
    }
    public function userOrders($id){
        $userOrder = Order::where('user_id',$id)->latest()->paginate(15);
        return view('admin.user_role.user_order',compact('userOrder'));
    }

    public function makeUser($id){
        $user = User::find($id);
        $user->update(['role_id' => 3]);

        $notification = array(
            'messege' =>  'Successfully User role Update at User',
            'alert-type' => 'success'
        );
        return back()->with($notification);
    }
    public function makeSeller($id){
        $user = User::find($id);
        $user->update(['role_id' => 2]);

        $notification = array(
            'messege' =>  'Successfully User role Update at Seller',
            'alert-type' => 'success'
        );
        return back()->with($notification);
    }

    public function makeAdmin($id){
        $user = User::find($id);
        $user->update(['role_id' => 1]);

        $notification = array(
            'messege' =>  'Successfully User role Update at Admin',
            'alert-type' => 'success'
        );
        return back()->with($notification);
    }

    // All Seller And Admin

    public function allSeller(){
        $sellers = User::where('role_id', 2)->paginate(15);
        return view('admin.user_role.all_seller',compact('sellers'));
    }

    public function allAdmin(){
        $admins = User::where('role_id', 1)->paginate(15);
        return view('admin.user_role.all_admin',compact('admins'));
    }

    public function deactiveUser($id){
        $user = User::find($id);
        $user->update([
            'deactive' => ($user->deactive == 1) ? 0 : 1
        ]);

        $notification = array(
            'messege' =>  'Successfully Update User Role',
            'alert-type' => 'success'
        );
       
        return back()->with($notification);
    }

    public function makeBestSeller($id){
        $user = User::find($id);

        $user->update([
            'best_seller' => ($user->best_seller == 1) ? 0 : 1
        ]);
        $notification = array(
            'messege' =>  'Successfully Update User Role',
            'alert-type' => 'success'
        );
       
        return back()->with($notification);
    }

    public function changePassword(Request $request,$id){
        $user = User::find($id);
        
        $newpass=$request->password;
        $confirm=$request->password_confirmation;
        
        if ($newpass === $confirm) {
            $user->password=Hash::make($request->password);
            $user->save();
            Auth::setUser($user);
            Auth::logout();  
            
            $notification=array(
            'messege'=>'Password Changed Successfully ! Now Login with Your New Password',
            'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification); 
        }
        else{

            $notification=array(
            'messege'=>'New password and Confirm Password not matched!',
            'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
                 
        }     
        
    }

}

