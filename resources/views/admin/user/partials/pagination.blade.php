<div class="d-flex justify-content-center mt-3">
    {{ $users->appends(request()->query())->links() }}
</div>