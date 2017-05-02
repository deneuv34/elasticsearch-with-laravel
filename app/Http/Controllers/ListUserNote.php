<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleUser as userModel;
use Basemkhirat\Elasticsearch\Facades\ES;

class ListUserNote extends Controller
{
    protected $model;

    // Controller for userModel
    public function __construct(userModel $model)
    {
        $this->model = $model;
        $this->esSearch = ES::index('user_index')->type('list')->get();
    }


    public function index(Request $req)
    {
        $result = $this->model->all();
        $result_es = $this->esSearch;

        return response()->json([
            'messsage' => 'Data retrieved!',
            'data' => $result->toArray(),
            'elastic_result' => $result_es,
        ]);
    }


    public function store(Request $req, userModel $user)
    {   
        // inser data
        $user->name = $req->name; // user.name = input.name
        $user->note = $req->note;
        $user->save(); // save to database
        
        // insert data into elasticSearch
        ES::index('user_index')->type('list')->id($user->getKey())->insert([
            'name' => $req->name,
            'note' => $req->note
        ]);

        // return response from controller
        return response()->json([
            'message' => 'Post Success!',
            'data' => $user,
        ]);
    }

    /** 
    *   POST /user/{id}
    *   body = name, note, _method
    */
    public function update(Request $req, $id)
    {
        // Update data sql
        $user = clone $this->model;
        $result = $user->find($id);

        $result->name = $req->name;
        $result->note = $req->note;
        $result->save();
    
        // Elasticsearch update
        ES::index('user_index')->type("list")->id($id)->update([
            "name" => $req->name,
            "note" => $req->note,
        ]);

        return response()->json([
            'data' => $result,
        ]);
    }

    /** 
    *   DELETE /user/{id}
    */
    public function destroy($id)
    {
        // delete data from database
        $user = clone $this->model;
        $result = $user->find($id);
        $result->delete();

        // Delete ElasticSearch Document
        $delete_es = ES::index('user_index')->type('list')->id($id)->delete();

        return response()->json([
            'message' => 'data is deleted!',
            'deleted' => $delete_es,
        ]);
    }

    public function show($id)
    {
        // Search model 
        $user = clone $this->model;
        $findResult = $user->find($id);
        $elastic = ES::index('user_index')->type('list')->id($id)->get();

        return response()->json([
            'message' => 'Success!',
            'data' => $findResult,
            'elastic_find' => $elastic,
        ]);
    }

    public function searchByQuery(Request $req, userModel $user)
    {
        // Search by query
        // Search model 
        $query = $req->search;
        $findResult = $user->where('name', 'LIKE', '%'.$query.'%')->get();
        // $elastic = ES::index('user_index')->type('list')->id($id)->get();

        return response()->json([
            'message' => 'Success!',
            'data' => $findResult,
            // 'elastic_find' => $elastic,
        ]);
    }
}
