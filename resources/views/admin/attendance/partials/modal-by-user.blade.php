<div class="modal fade" id="modalByUser" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Choose User</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="user_search">Search User:</label>
          <input type="text" class="form-control" id="user_search" placeholder="Type to search users..." autocomplete="off">
        </div>
        <div class="form-group">
          <label for="user_id">Select User:</label>
          <select class="form-control" name="user_id" id="user_id" size="5" style="height: 150px;">
              @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->role_name ?? '-' }})</option>
              @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-save">Save</button>
      </div>
    </div>
  </div>
</div>
