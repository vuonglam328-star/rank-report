@extends('layouts.app')
@section('title', 'Quản lý Users')
@section('page-title', 'Quản lý Users')
@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
</div>
@endif

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Danh sách Users</h3>
        <div class="card-tools ml-auto">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createUserModal">
                <i class="fas fa-user-plus mr-1"></i>Thêm User
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" style="font-size:.9rem;">
            <thead class="thead-light">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th style="width:100px;">Role</th>
                    <th style="width:140px;">Ngày tạo</th>
                    <th style="width:110px;" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr class="{{ $u->id === auth()->id() ? 'table-active' : '' }}">
                    <td class="text-muted">{{ $u->id }}</td>
                    <td>
                        {{ $u->name }}
                        @if($u->id === auth()->id())
                            <span class="badge badge-light border ml-1">bạn</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $u->email }}</td>
                    <td>
                        <span class="badge badge-{{ $u->roleBadgeColor() }}">{{ $u->role }}</span>
                    </td>
                    <td class="text-muted small">{{ $u->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-warning btn-edit-user"
                                data-id="{{ $u->id }}"
                                data-name="{{ $u->name }}"
                                data-email="{{ $u->email }}"
                                data-role="{{ $u->role }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline"
                              onsubmit="return confirm('Xóa user \"{{ $u->name }}\"?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Chưa có user nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->links('pagination::bootstrap-4') }}</div>
    @endif
</div>

{{-- ── Modal Tạo User ───────────────────────────────────────────────────────── --}}
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Thêm User mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Tên <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="255"
                               value="{{ old('name') }}" placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required maxlength="255"
                               value="{{ old('email') }}" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8"
                               placeholder="Tối thiểu 8 ký tự">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Phân quyền <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="viewer"  {{ old('role') === 'viewer'  ? 'selected' : '' }}>Viewer — chỉ xem</option>
                            <option value="editor"  {{ old('role') === 'editor'  ? 'selected' : '' }}>Editor — xem + import + sửa</option>
                            <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin — toàn quyền</option>
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <strong>Viewer:</strong> chỉ xem &nbsp;|&nbsp;
                            <strong>Editor:</strong> xem + import CSV + tạo/sửa &nbsp;|&nbsp;
                            <strong>Admin:</strong> toàn quyền + quản lý users
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Tạo User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Sửa User ───────────────────────────────────────────────────────── --}}
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-user-edit mr-2"></i>Sửa User</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="" id="editUserForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Tên <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Mật khẩu mới <span class="text-muted">(để trống nếu không đổi)</span></label>
                        <input type="password" name="password" class="form-control" minlength="8" placeholder="Tối thiểu 8 ký tự">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Phân quyền <span class="text-danger">*</span></label>
                        <select name="role" id="editRole" class="form-control" required>
                            <option value="viewer">Viewer — chỉ xem</option>
                            <option value="editor">Editor — xem + import + sửa</option>
                            <option value="admin">Admin — toàn quyền</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i>Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const name  = this.dataset.name;
        const email = this.dataset.email;
        const role  = this.dataset.role;

        document.getElementById('editUserForm').action = `/users/${id}`;
        document.getElementById('editName').value  = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value  = role;

        // Clear password fields
        document.querySelectorAll('#editUserModal input[type=password]').forEach(i => i.value = '');

        $('#editUserModal').modal('show');
    });
});
</script>
@endpush
