<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">AJAX Task Manager</h2>

    <div class="d-flex justify-content-between mb-3">
        <input type="text" id="searchInput" class="form-control w-50" placeholder="Search by title...">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Task</button>
    </div>

    <div id="taskTableWrapper"></div>
</div>

{{-- Add Task Modal --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addTaskForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Create</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Task Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editTaskForm">
      <input type="hidden" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let currentPage = 1;

    function loadTasks(page = 1, search = '') {
        $.get('/fetch-tasks?page=' + page + '&search=' + search, function(res) {
            let rows = '';
            $.each(res.tasks.data, function(i, task) {
                rows += `
                <tr>
                    <td><input type="checkbox" data-id="${task.id}" class="toggle-complete" ${task.completed ? 'checked' : ''}></td>
                    <td>${task.title}</td>
                    <td>${task.description ?? ''}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${task.id}" data-title="${task.title}" data-description="${task.description}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${task.id}">Delete</button>
                    </td>
                </tr>`;
            });

            let pagination = res.tasks.links.map(link =>
                `<li class="page-item ${link.active ? 'active' : ''}">
                    <a class="page-link page-link-btn" href="#" data-page="${link.url ? link.url.split('page=')[1] : '#'}">${link.label}</a>
                </li>`
            ).join('');

            $('#taskTableWrapper').html(`
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>✔</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
                <nav><ul class="pagination">${pagination}</ul></nav>
            `);
        });
    }

    loadTasks();

    $('#searchInput').on('input', function() {
        loadTasks(1, $(this).val());
    });

    $(document).on('click', '.page-link-btn', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadTasks(page, $('#searchInput').val());
        }
    });

    $('#addTaskForm').submit(function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.post('/tasks', data, function(res) {
            $('#addModal').modal('hide');
            $('#addTaskForm')[0].reset();
            loadTasks(currentPage);
        });
    });

    $(document).on('click', '.edit-btn', function() {
        $('#editTaskForm [name=id]').val($(this).data('id'));
        $('#editTaskForm [name=title]').val($(this).data('title'));
        $('#editTaskForm [name=description]').val($(this).data('description'));
        $('#editModal').modal('show');
    });

    $('#editTaskForm').submit(function(e) {
        e.preventDefault();
        const id = $('#editTaskForm [name=id]').val();
        const data = $(this).serialize();
        $.ajax({
            url: '/tasks/' + id,
            type: 'PUT',
            data: data,
            success: function(res) {
                $('#editModal').modal('hide');
                loadTasks(currentPage);
            }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        if (confirm('Delete this task?')) {
            const id = $(this).data('id');
            $.ajax({
                url: '/tasks/' + id,
                type: 'DELETE',
                success: function(res) {
                    loadTasks(currentPage);
                }
            });
        }
    });

    $(document).on('change', '.toggle-complete', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `/tasks/${id}/toggle`,
            type: 'PATCH',
            success: function(res) {
                loadTasks(currentPage);
            }
        });
    });
</script>
</body>
</html>
