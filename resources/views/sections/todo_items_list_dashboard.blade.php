<div class="rd-todo-inner text-left">
    <div class="px-4 py-3 border-b border-[#F0EEE9]">
        <div class="flex items-center gap-2 bg-[#F5F6FA] rounded-xl px-3 py-2">
            <span class="flex-1 text-[12.5px] text-[#8892A0] truncate">@lang('modules.module.todos.addTaskHint')</span>
            <button type="button" onclick="showNewTodoForm()" class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 text-white" style="background: var(--ra-accent, #2563eb);" title="@lang('app.add')" aria-label="@lang('app.add')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>
    </div>
    <div class="px-4 pt-3 pb-1 max-h-[220px] overflow-y-auto">
        <p class="text-[10px] font-bold tracking-widest uppercase text-[#8892A0] mb-2">@lang('modules.module.todos.pendingTasks')</p>
        <ul class="rd-todo-ul space-y-0" id="pending-tasks">
            @forelse ($pendingTodos as $todo)
                <li data-id="{{ $todo->id }}" data-position="{{ $todo->position }}" class="draggable rd-todo-li group">
                    <div class="flex items-start gap-2 py-2 border-b border-[#F0EEE9] last:border-0">
                        <span class="handle flex items-center pt-0.5 text-[#C4CBD4] cursor-grab active:cursor-grabbing shrink-0" aria-hidden="true"><i class="fa fa-bars text-xs"></i></span>
                        <input data-id="{{ $todo->id }}" type="checkbox" name="status" id="status-{{ $todo->id }}" class="mt-0.5 rounded border-[#D1D9E8] text-[var(--ra-accent,#2563eb)] focus:ring-[var(--ra-accent,#2563eb)]">
                        <div class="flex-1 min-w-0">
                            <label for="status-{{ $todo->id }}" class="text-[12.5px] font-medium text-[#3D4A5C] cursor-pointer block">{{ $todo->title }}</label>
                            <p class="text-[11px] text-[#8892A0] mt-0.5">{{ $todo->created_at->format('Y-m-d') }}</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" onclick="showUpdateTodoForm('{{ $todo->id }}')" class="p-1.5 rounded-lg text-[#2563EB] hover:bg-[#EFF6FF]" aria-label="@lang('app.edit')"><i class="fa fa-pencil text-xs"></i></button>
                            <button type="button" onclick="deleteTodoItem('{{ $todo->id }}')" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" aria-label="@lang('app.delete')"><i class="fa fa-trash-o text-xs"></i></button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-6 text-center text-[11.5px] text-[#C4CBD4]" id="no-pending-task">@lang('modules.module.todos.noPendingTasks')</li>
            @endforelse
        </ul>
    </div>
    <div class="border-t border-[#F0EEE9] px-4 pt-3 pb-3 max-h-[200px] overflow-y-auto">
        <p class="text-[10px] font-bold tracking-widest uppercase text-[#8892A0] mb-2">@lang('modules.module.todos.completedTasks')</p>
        <ul class="rd-todo-ul space-y-0" id="completed-tasks">
            @forelse ($completedTodos as $todo)
                <li data-id="{{ $todo->id }}" data-position="{{ $todo->position }}" class="draggable rd-todo-li group opacity-90">
                    <div class="flex items-start gap-2 py-2 border-b border-[#F0EEE9] last:border-0">
                        <span class="handle flex items-center pt-0.5 text-[#C4CBD4] cursor-grab active:cursor-grabbing shrink-0" aria-hidden="true"><i class="fa fa-bars text-xs"></i></span>
                        <input data-id="{{ $todo->id }}" checked type="checkbox" name="status" id="status-c-{{ $todo->id }}" class="mt-0.5 rounded border-[#D1D9E8] text-[var(--ra-accent,#2563eb)] focus:ring-[var(--ra-accent,#2563eb)]">
                        <div class="flex-1 min-w-0">
                            <label for="status-c-{{ $todo->id }}" class="text-[12.5px] text-[#B0B8C4] line-through cursor-pointer block">{{ $todo->title }}</label>
                            <p class="text-[11px] text-[#8892A0] mt-0.5">{{ $todo->created_at->format('Y-m-d') }}</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" onclick="showUpdateTodoForm('{{ $todo->id }}')" class="p-1.5 rounded-lg text-[#2563EB] hover:bg-[#EFF6FF]" aria-label="@lang('app.edit')"><i class="fa fa-pencil text-xs"></i></button>
                            <button type="button" onclick="deleteTodoItem('{{ $todo->id }}')" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" aria-label="@lang('app.delete')"><i class="fa fa-trash-o text-xs"></i></button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-6 text-center text-[11.5px] text-[#C4CBD4]" id="no-completed-task">@lang('modules.module.todos.noCompletedTasks')</li>
            @endforelse
        </ul>
    </div>
</div>
