<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
//Models
use App\Models\TasksModel;
use App\Models\UsersModel;


class TasksController extends Controller {
    public function __construct(public Request $request) {}

    public function getTask() {
        $queryParam = $this->request->array();
        //associative array destructuring
        @['_user_id' => $_user_id, 'page' => $page, 'limit' => $limit] = $queryParam;

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

        } catch (\Exception $err) {
        Log::error($e->getMessage());
        return Response::noContent(500);
        }
    }

    public function searchTask() {
      $queryArray = $this->request->array();
      @['_user_id' => $_user_id, 'keyword' => $keyword, 'page' => $page, 'limit' => $limit] = $queryArray;

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
      } catch (\Exception $err) {
        Log::error($e->getMessage());
        return Response::noContent(500);
      }
    }

    public function filterTask() {
        $queryParam = $this->request->array();
        //associative array destructuring
        @['_user_id' => $_user_id, 'addedBefore' => $addedBefore, 'addedAfter' => $addedAfter, 'taskSubject' => $taskSubject, 'taskDone' => $taskDone, 'page' => $page, 'limit' => $limit,] = $queryParam;

        if (!isset($_user_id) || !isset($page) || !isset($limit)) return Response::json(['response' => 'missing-parameters'], 400);
        if (isset($addedBefore)) {
            list($date, $useless) = explode(' GMT', $addedBefore);
            $addedBefore = strtotime($date);

        }
        if (isset($addedAfter)) {
            list($date, $useless) = explode(' GMT', $addedAfter);
            $addedAfter = strtotime($date);
        }

        $filtersObject = [
            'addedBefore' => isset($addedBefore) ? $addedBefore : null,
            'addedAfter' => isset($addedAfter) ? $addedAfter : null,
            'taskSubject' => isset($taskSubject) ? $taskSubject : null,
            'taskDone' => isset($taskDone) ? $taskDone : null
        ];

        try {
            $user = UsersModel::get()->where('_id', $_user_id);
            $tasks = TasksModel::get()->where('_user_id', $_user_id);

            if (count($user) < 1) return Response::json(["response" => 'user-not-found'], 401);
            if (count($tasks) < 1) return Response::json(["response" => 'not-tasks'], 204);
            $taskDone = $filtersObject['taskDone'];
            switch ($taskDone) {
                case 'Y': {
                  $taskDone = true;
                  break;
                }
                case 'N': {
                 $taskDone = false;
                 break;
                }
                default: {
                 $taskDone = null;
                 break;
                }
            }

            $filteredTasks = [];

            foreach($tasks as $task) {
                $bool = false;

                $addingDate = $task['addDate'] * 1000;;
                $beforeDate = $addedBefore * 1000;
                $afterDate = $addedAfter * 1000;

                if ($beforeDate != 0) {
                  if ($beforeDate > $addingDate) {
                    $bool = true;
                } else continue;
                }

                if ($afterDate != 0) {
                  if ($afterDate < $addingDate) $bool = true;
                  else continue;
                }

                if ($filtersObject['taskSubject'] != null) {

                  if ($filtersObject['taskSubject'] === $task['type']) {

                    $bool = true;
                }
                  else continue;
                }

                if ($filtersObject['taskDone'] != null) {
                    $doneBool = false;
                    $task['done'] === 0 ? $doneBool : !$doneBool;
                    if ($taskDone === $doneBool) {
                        $bool = true;
                    }
                    else continue;
                }

                if ($bool) $filteredTasks[] = $task;
            }

            if (count($filteredTasks) < 1) return Response::noContent();

            $pageIndex = (int)$page;
            $pageSize = (int)$limit;

            $taskStartingIndex = $pageIndex - 1 === 0 ? 0 : ($pageIndex - 1) * $pageSize;
            //array_slice just needs the amout of values to take from the array in the third parameter differently from js slice method that needs the ending index
            $tasksResponse = array_slice($filteredTasks, $taskStartingIndex, $pageSize);

            return Response::json([
                'filteredTasks' => $tasksResponse,
                'totalFilteredTasks' => count($filteredTasks)
            ], 200);
        } catch (\ErrorException $e) {
            Log::error($e->getMessage());
            return Response::noContent(500);
        }
    }

    public function postTask() {
        //name is TYPE
        @['_user_id' => $_user_id, 'name' => $type, 'label' => $label, 'description' => $description, 'done' => $done] = $this->request->all();

        if (!isset($_user_id) || !isset($type) || !isset($label) || !isset($description) || !isset($done)) return Response::json(["response" => "missing-parameters"], 400);

        try {
            $taskObject = [
                'id' => null,
                '_user_id' => $_user_id,
                'type' => $type,
                'label' => $label,
                'description' => $description,
                'done' => $done,
                'addDate' => time(),
            ];
            $dueDate = $this->request->input('dueDate');
            if (isset($dueDate)) $taskObject['dueDate'] = $dueDate;

            $user = UsersModel::get()->where('_id', $_user_id);

            if (count($user) < 1) return Response::json(["response" => "user-not-found"], 401);

            TasksModel::insert($taskObject);

            return Response::json(["response" => "task-added"], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Response::noContent(500);
        }
    }

    public function deleteTask(string $id) {
        try {
            $task = TasksModel::get()->where('id', $id);

            if (count($task) < 1) return Response::json(["response" => "task-not-found"], 204);

            TasksModel::where('id', $id)->delete();

            return Response::json(["response" => "task-deleted", 200]);
        } catch (\Exception $e) {
          Log::error($e->getMessage());
          return Response::noContent(500);
        }
    }

    public function updateTask() {
        @['idTask' => $idTask, 'description' => $description] =$this->request->all();

        if (@!$idTask || @!$description) return Response::json(["response" => "missing-parameters"], 400);
        $taskDone = $this->request->input('taskDone');
        $dueDate = $this->request->input('dueDate');

        $updatedTask = [
            "description" => $description,
/*             "taskDone" => isset($taskDOne) ? $taskDone : null,
            "dueDate" => isset($dueDate) ? $dueDate : null */
        ];

        if (@$taskDone) {
            switch ($taskDone) {
                case 'Y': {
                    $updatedTask["done"] = true;
                    break;
                }
                default: {
                    $updatedTask["done"] = false;
                }
            }
        }

        if (@$dueDate) $updatedTask["dueDate"] = $dueDate;
        try {
            $task = TasksModel::get()->where('id', $idTask);

            if (!isset($task)) return Response::json(["response" => "task-not-found"],401);

            TasksModel::where('id', $idTask)->update($updatedTask);

            return Response::json(["response" => "task-updated"], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Response::noContent(500);
        }
    }
}
