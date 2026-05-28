<div class="flex justify-between items-center mb-3">
    <h3 class="text-lg font-semibold text-gray-900">
        @lang('modules.module.todos.todoList')
    </h3>
    <a href="{{ route('admin.todo-items.index') }}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        @lang('modules.module.todos.viewAll')
    </a>
</div>

<div id="upper-box" class="bg-white rounded-lg shadow-md mb-3">
    <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h5 class="font-semibold text-gray-900">@lang('modules.module.todos.pendingTasks')</h5>
        <a href="javascript:showNewTodoForm();" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"><i class="fa fa-plus"></i></a>
    </div>
    <ul class="divide-y divide-gray-200" id="pending-tasks">
        @forelse ($pendingTodos as $todo)
            <li data-id="{{ $todo->id }}" data-position="{{ $todo->position }}" class="draggable px-4 py-3 hover:bg-gray-50 transition-colors">
               
                <div class="flex items-start">
                    <span class="mb-2 flex items-center">
                        <input data-id="{{ $todo->id }}" type="checkbox" name="status" id="status-{{ $todo->id }}" class="mr-2 rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="status-{{ $todo->id }}" class="text-gray-900 font-medium">{{ $todo->title }}</label>
                    </span>
                </div>
                <div class="flex justify-between items-center flex-wrap mt-2">
                    <p class="mb-0 text-sm text-gray-500">{{ $todo->created_at->format('Y-m-d') }}</p>
                    <div class="flex items-center space-x-2">
                        <a href="javascript:showUpdateTodoForm('{{ $todo->id }}');" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><i class="fa fa-pencil-square-o"></i></a>
                        <a href="javascript:deleteTodoItem('{{ $todo->id }}');" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"><i class="fa fa-trash-o"></i></a>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-3" id="no-pending-task">
                <h6 class="text-sm font-medium text-gray-500 text-center">
                    @lang('modules.module.todos.noPendingTasks')
                </h6>
            </li>
        @endforelse
    </ul>
</div>
<div id="lower-box" class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
        <h5 class="font-semibold text-gray-900">@lang('modules.module.todos.completedTasks')</h5>
    </div>
    <ul class="divide-y divide-gray-200" id="completed-tasks">
        @forelse ($completedTodos as $todo)
            <li data-id="{{ $todo->id }}" data-position="{{ $todo->position }}" class="draggable px-4 py-3 hover:bg-gray-50 transition-colors opacity-75">
               
                <div class="flex items-start">
                    <span class="mb-2 flex items-center">
                        <input data-id="{{ $todo->id }}" checked type="checkbox" name="status" id="status-{{ $todo->id }}" class="mr-2 rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="status-{{ $todo->id }}" class="text-gray-600 line-through">{{ $todo->title }}</label>
                    </span>
                </div>
                <div class="flex justify-between items-center flex-wrap mt-2">
                    <p class="mb-0 text-sm text-gray-500">{{ $todo->created_at->format('Y-m-d') }}</p>
                    <div class="flex items-center space-x-2">
                        <a href="javascript:showUpdateTodoForm('{{ $todo->id }}');" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><i class="fa fa-pencil-square-o"></i></a>
                        <a href="javascript:deleteTodoItem('{{ $todo->id }}');" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"><i class="fa fa-trash-o"></i></a>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-3" id="no-completed-task">
                <h6 class="text-sm font-medium text-gray-500 text-center">
                    @lang('modules.module.todos.noCompletedTasks')
                </h6>
            </li>
        @endforelse
    </ul>
</div>

