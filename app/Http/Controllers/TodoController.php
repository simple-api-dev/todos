<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TodoController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/todos",
     *     summary="Retrieve all todos",
     *     operationId="todo.index",
     *     tags={"Generic Todo"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Response(response="200", description="A collection of todos")
     * )
     */
    /**
     * @OA\Get(
     *     path="/api/users/{user_id}/todos",
     *     summary="Retrieve all todos",
     *     operationId="user.todo.index",
     *     tags={"User Todo"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="A collection of todos for current user",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Response(response="200", description="Collection of todos")
     * )
     */
    public function index(Request $request): JsonResponse{


        $query = Todo::where("integration_id", $this->integration_id);

        if($request->has('completed')){
            if(strtolower($request->get('completed')) == "true" || strtolower($request->get('completed')) == "false") {
                    $query->where("completed", strtolower($request->get('completed')) == "true");
                }
        }

        if($this->secure_mode && $this->current_user){
            $query->where('user_id', $this->current_user->id);
        }

        $results = $query->get();

        if(!$this->secure_mode){
            $results->makeHidden(['user_id', 'author']);
        }

        //$results->makeHidden(['meta']);
        return response()->json($results,200);
    }

    /**
     * @OA\Get(
     *     path="/api/todos/{id}",
     *     summary="Retrieve specific todo",
     *     operationId="todo.get",
     *     tags={"Generic Todo"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the todo you wish to retrieve",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Response(response="200", description="A todo")
     * )
     */
    /**
     * @OA\Get(
     *     path="/api/users/{user_id}/todos/{id}",
     *     summary="Retrieve specific todo for current user",
     *     operationId="user.todo.get",
     *     tags={"User Todo"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Todo Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Response(response="200", description="A todo belonging to current user")
     * )
     */
    public function get(Request $request, int $user_id, int $id): JsonResponse{

        $query = Todo::where("integration_id", $this->integration_id);
        $query->where('id',$id);

        if($this->secure_mode && $this->current_user){
            $query->where('user_id', $this->current_user->id);
        }

        $result = $query->first();

        if(!$this->secure_mode){
            $result->makeHidden(['user_id', 'author']);
        }
        if($result)
            return response()->json($result);
        else
            return response()->json("Todo ${id} not found.", 404);
    }

    /**
     * @OA\Post(
     * path="/api/todos",
     * summary="Create a new Todo.  There's a limit of 100 generic todos per developer account.",
     * operationId="Todo.create",
     * tags={"Generic Todo"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Submit a todo description and optionally a completion flag",
     *    @OA\JsonContent(
     *       required={"description"},
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *     @OA\Property(property="meta", type="object")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Todo created successfully",
     *    @OA\JsonContent(
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *     @OA\Property(property="meta", type="object")
     *        )
     *     )
     * )
     */
    /**
     * @OA\Post(
     * path="/api/users/{user_id}/todos",
     * summary="Create a new Todo for current user.  There's a limit of 25 todos per user .",
     * operationId="user.todo.create",
     * tags={"User Todo"},
     * security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Submit a todo description and optionally a completion flag",
     *    @OA\JsonContent(
     *       required={"description"},
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *     @OA\Property(property="meta", type="object")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Todo created successfully",
     *    @OA\JsonContent(
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *     @OA\Property(property="meta", type="object")
     *        )
     *     )
     * )
     */
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function post(Request $request) : JsonResponse{

        $this->validate($request, [
            'description' => 'required',
            'completed' => 'boolean',
            'meta' => 'array'
        ]);

        // Limit the total todos per account
        if($this->current_user){
            $limit_reached = Todo::where('user_id',$this->current_user->id)->count() > 25;
            $message = "Maximum number of todos (25) per user has been reached.";
        }
        else{
            $limit_reached = Todo::where('integration_id',$this->integration_id)->count() > 100;
            $message = "Maximum number of todos (100) per developer account has been reached.";
        }

        if($limit_reached){
            return response()->json(['message' => $message], 403);
        }

        $todo = Todo::make($request->all());
        $todo = new Todo();
        $todo->description = $request->description;
        $todo->integration_id = $this->integration_id;
        $todo->completed = $description->completed ?? false;
        $todo->user_id = $this->current_user?->id;
        $todo->author = $this->current_user?->name;
        if($request->meta && $this->json_validator($request->meta)){
            $size = mb_strlen(json_encode($request->meta, JSON_NUMERIC_CHECK), '8bit');
            if($size > 256){
                return response()->json(['message' => "Meta data field cannot exceed 256 bytes"], 403);
            }
            $todo->meta =json_encode($request->meta);
        }
        else
        {
            $todo->meta = null;
        }

        $todo->save();
        if(!$todo->user_id){
            unset($todo->user_id);
            unset($todo->author);
        }
        if(!$todo->meta)
            unset($todo->meta);

        return response()->json($todo);
    }


    private function json_validator($data=NULL) : bool {

        if (!empty($data)) {

            @json_encode($data);

            return (json_last_error() === JSON_ERROR_NONE);

        }
        return false;
    }
    /**
     * @OA\Put(
     * path="/api/todos/{id}",
     * summary="Create a new Todo",
     * operationId="Todo.update",
     * tags={"Generic Todo"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Todo ID",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Submit a todo description and optionally a completion flag",
     *    @OA\JsonContent(
     *       required={"description"},
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *     @OA\Property(property="meta", type="object")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Todo updated successfully",
     *    @OA\JsonContent(
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *     @OA\Property(property="meta", type="object")
     *        )
     *     )
     * )
     */
    /**
     * @OA\Put(
     *     path="/api/users/{user_id}/todos/{id}",
     *     summary="Create a new Todo for the current user",
     *     operationId="Todo.user.update",
     *     tags={"User Todo"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Todo ID",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Submit a todo description and optionally a completion flag",
     *    @OA\JsonContent(
     *       required={"description"},
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Todo updated successfully",
     *    @OA\JsonContent(
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="completed", type="boolean"),
     *        )
     *     )
     * )
     */
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function put($id, Request $request): JsonResponse
    {

        $todo = Todo::find($id);
        if($todo && $todo->integration_id == $this->integration_id &&
            ($todo->user_id == null || $todo->user_id === $this->current_user->id )
            ) {
            $this->validate($request, [
                'description' => 'required',
                'completed' => 'boolean',
                'meta' => 'array'
            ]);

            $todo->description = $request->description;
            $todo->completed = $request->completed ?? false;

            if ($request->meta && $this->json_validator($request->meta)) {
                $size = mb_strlen(json_encode($request->meta, JSON_NUMERIC_CHECK), '8bit');
                if($size > 512){
                    return response()->json(['message' => "Meta data field cannot exceed 512 bytes"], 403);
                }
                $todo->meta =json_encode($request->meta);
            }
            else
            {
                $todo->meta = null;
            }

            $todo->save();

            if(!$todo->user_id){
                unset($todo->user_id);
                unset($todo->author);
            }

            if(!$todo->meta)
                unset($todo->meta);

            return response()->json($todo, 200);
        }
        else {
            $message = ['message' => "Todo not found ({$id})"];
            return response()->json($message, 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/todo/{id}",
     *     summary="Deletes the todo.",
     *     operationId="TodoController.Remove",
     *     tags={"Generic Todo"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Todo ID",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Response(response="200", description="Returns a message")
     * )
     */
    /**
     * @OA\Delete(
     *     path="/api/users/{user_id}/todo/{id}",
     *     summary="Deletes the todo, must belong to current user.",
     *     operationId="Todo.user.remove",
     *     tags={"User Todo"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Todo ID",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Response(response="200", description="Returns a message")
     * )
     */
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function remove($id) : JsonResponse
    {

        $todo = Todo::find($id);
        if ($this->secure_mode) {
            // can only delete your own if you are logged in
            if ($todo && $todo->integration_id == $this->integration_id && $todo->user_id == $this->current_user->id) {
                $todo->delete();
                $message = ['message' => "Todo deleted ({$id})"];
                return response()->json($message, 200);
            }
        } else if ($todo && $todo->integration_id == $this->integration_id) {
            $todo->delete();
            $message = ['message' => "Todo deleted ({$id})"];
            return response()->json($message, 200);
        }

        $message = ['message' => "Todo not found ({$id})"];
        return response()->json($message, 404);

    }

    /**
     * @OA\Delete(
     *     path="/api/todos/",
     *     summary="Deletes all Todos",
     *     operationId="Todo.removeAll",
     *     tags={"Generic Todo"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="completed",
     *         in="query",
     *         description="?completed=true if you want to delete only completed",
     *         required=false,
     *         @OA\Schema(type="boolean"),
     *      ),
     *     @OA\Response(response="200", description="Returns message of outcome")
     * )
     */
    /**
     * @OA\Delete(
     *     path="/api/users/{user_id}/todos/",
     *     summary="Deletes all Todos for the current user.",
     *     operationId="Todo.user.removeAll",
     *     tags={"User Todo"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="completed",
     *         in="query",
     *         description="?completed=true if you want to delete only completed",
     *         required=false,
     *         @OA\Schema(type="boolean"),
     *      ),
     *     @OA\Response(response="200", description="Returns message of outcome")
     * )
     */
    public function removeAll(Request $request): JsonResponse {
        // check for completed flag in querystring
        if($request->has('completed')) {
            $key = $request->get('completed') == "true";
        }
        // this will delete all todos either belonging to current user:
        if ($this->current_user){
            $query = DB::table('todos')->where('integration_id', $this->integration_id)
                ->where('user_id',$this->current_user->id);
            if(isset($key)) {
                $query->where('completed', $key);
            }
            $query->delete();
            return response()->json(["message"=>"All my" . (isset($key) ? " completed " : " ") . "todos have been deleted."]);
        }
        // or it will delete all anonymous todos:
        else{
            $query = DB::table('todos')->where('integration_id', $this->integration_id);
            if(isset($key)) {
                $query->where('completed', $key);
            }
            $query->whereNull('user_id')->delete();
            $message = ['message' => "All anonymous" . (isset($key) ? " completed " : " ") . "todos deleted."];
            return response()->json($message);
        }
    }


}
