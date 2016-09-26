<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UserRequest;
use App\Http\Requests;
use App\User;
use DB;

class UserController extends Controller
{
    // return index list based on get request
    public function indexApi(Request $request)
    {
        $name_field = $request->name;
        $email_field = $request->email;
        $created_at_field = $request->created_at;
        $sortkey = $request->sortkey;
        $results = User::whereNotNull('created_at');
/*
        $results = DB::table('users')
                    ->when($name_field, function($query) use ($name_field) {
                        return $query->where('name', 'LIKE', '%'.$name_field.'%');
                    })->when($email_field, function($query) use ($email_field) {
                        return $query->where('email', 'LIKE', '%'.$email_field.'%');
                    })->when($created_at_field, function($query) use ($created_at_field) {
                        return $query->whereDate('created_at', $created_at_field);
                    });*/

        if($name_field) {
            $results = $results->where('name', 'LIKE', '%'.$name_field.'%');
        }
        if($email_field) {
            $results = $results->where('email', 'LIKE', '%'.$email_field.'%');
        }
        if($created_at_field) {
            $results = $results->whereDate('created_at', $created_at_field);
        }

        if($sortkey) {
            $results = $results->orderBy($sortkey, $request->reverse == 'true' ? 'asc' : 'desc');
        }else{
            $results = $results->latest();
        }

        $results = $results->paginate($request->perpage);

        $response = [
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem()
            ],
            'data' => $results
        ];
        return $response;
    }
    // store new creation
    public function store(UserRequest $request)
    {
        $request->merge(array('password' => bcrypt('secret')));
        $user = User::create($request->all());
        return $user->toJson();
    }

    // return edit view
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return $user;
    }

    // update user attributes
    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());
    }

    // delete user entry on index
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }

}
