<?php

namespace App\Http\Controllers;

use App\Exports\TodosExport;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $todos = $this->applyFilters(Todo::query(), $request)->latest()->get();

        return TodoResource::collection($todos);
    }

    public function store(StoreTodoRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'pending';
        $data['time_tracked'] = $data['time_tracked'] ?? 0;

        $todo = Todo::create($data);

        return (new TodoResource($todo))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Todo $todo)
    {
        return new TodoResource($todo);
    }

    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        $todo->update($request->validated());

        return new TodoResource($todo);
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();

        return response()->json(['message' => 'Todo deleted successfully']);
    }

    public function exportExcel(Request $request)
    {
        $todos = $this->applyFilters(Todo::query(), $request)->latest()->get();

        $fileName = 'todos_report_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new TodosExport($todos), $fileName);
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->query('title') . '%');
        }

        if ($request->filled('assignee')) {
            $assignees = array_map('trim', explode(',', $request->query('assignee')));
            $query->whereIn('assignee', $assignees);
        }

        if ($request->filled('start') || $request->filled('end')) {
            $start = $request->query('start');
            $end = $request->query('end');

            if ($start && $end) {
                $query->whereBetween('due_date', [$start, $end]);
            } elseif ($start) {
                $query->where('due_date', '>=', $start);
            } elseif ($end) {
                $query->where('due_date', '<=', $end);
            }
        }

        if ($request->filled('min') || $request->filled('max')) {
            $min = $request->query('min');
            $max = $request->query('max');

            if ($min !== null && $max !== null) {
                $query->whereBetween('time_tracked', [$min, $max]);
            } elseif ($min !== null) {
                $query->where('time_tracked', '>=', $min);
            } elseif ($max !== null) {
                $query->where('time_tracked', '<=', $max);
            }
        }

        if ($request->filled('status')) {
            $statuses = array_map('trim', explode(',', $request->query('status')));
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('priority')) {
            $priorities = array_map('trim', explode(',', $request->query('priority')));
            $query->whereIn('priority', $priorities);
        }

        return $query;
    }
}
