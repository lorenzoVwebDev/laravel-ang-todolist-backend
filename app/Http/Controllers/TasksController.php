<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
//Models
use App\Models\TasksModel;
use App\Models\UsersModel;
use App\Models\UsersPasswordsModel;


class TasksController extends Controller
{
    public function __construct(public Request $request) {}

    public function getTask() {
        $queryParam = $this->request->array();
        //associative array destructuring
        ['_user_id' => $_user_id, 'page' => $page, 'limit' => $limit] = $queryParam;
        
        if (!$_user_id || !$page || !$limit) return response()->json(['response' => 'missing-parameters'], 400);
        
        try {
          $allTasks = TasksModel::get();
          $allUsers = UsersModel::get();

          $tasksUser = [];

          foreach($allUsers as $user) {
            if ($user['_id'] === $_user_id) {
              $tasksUser[] = $user;
            } 
          }

          if (count($tasksUser) < 1) return Response::json(['response' => 'user-not-found']); 

          $userTasks = [];

          foreach($allTasks as $task) {
            if ($task['_user_id'] === $_user_id) $userTasks[] = $task;
          }

          if (count($userTasks) < 1) return Response::noContent();

          $pageIndex = (int)$page;
          $pageSize = (int)$limit;
          
          $taskStartingIndex = $pageIndex - 1 === 0 ? 0 : ($pageIndex - 1) * $pageSize;
          //array_slice just needs the amout of values to take from the array in the third parameter differently from js slice method that needs the ending index
          $tasksResponse = array_slice($userTasks, $taskStartingIndex, $pageSize);

          return Response::json([
            'tasks' => $tasksResponse,
            'totalUserTasks' => count($userTasks)
          ], 200);
    
        } catch (Exception $err) {
          return Response::noContent(500); 
        }
    }

    public function searchTask() {
      $queryArray = $this->request->array();
      ['_user_id' => $_user_id, 'keyword' => $keyword, 'page' => $page, 'limit' => $limit] = $queryArray;

      if (!$_user_id || !$keyword || !$page || !$limit) return Response::json(['response' => 'missing-parameters'], 400);

      try {
      //query builder, we can chain methods to create query likely: "SELECT * FROM tasks WHERE name = 'lorenzo';
      $user = UsersModel::get()->where('_id', $_user_id);
      if (count($user) < 1) return Response::json(['respone' => 'user-not-found']);

      $userTasks = TasksModel::where('_user_id', $_user_id)->get();
      
      if (count($user) < 1) return Response::json(['response' => 'no-tasks'], 204);
      $filteredTasks = [];

      for ($i = 0; $i <= (count($userTasks) - 1); $i++) {
        if (str_contains(strtolower($userTasks[$i]->name), strtolower($keyword)) || str_contains(strtolower($userTasks[$i]->description), strtolower($keyword))) $filteredTasks[] = $userTasks[$i];
      }

      if (count($filteredTasks) < 1) return Response::json(['response'=>'no-filtered-task'], 204);

      $pageIndex = (int)$page;
      $pageSize = (int)$limit;

      $taskStartingIndex = $pageIndex === 1 ? 0 : ($pageIndex - 1) * $pageSize;
      $tasksResponse = array_slice($filteredTasks, $taskStartingIndex, $pageSize);

      return Response::json([
        'tasks' => $tasksResponse,
        'totalUserTasks' => count($userTasks)
      ], 200);
      } catch (Exception $err) {
        return Response::noContent(500);
      }
    }
}
