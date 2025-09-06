<div class="modal fade" id="modalByRole" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Choose Role</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <select class="form-control" name="role_id" id="role_id">
            @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
            @endforeach
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-save">Save</button>
      </div>
    </div>
  </div>
</div>
