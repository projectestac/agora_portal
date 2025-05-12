<div style="text-align: right; margin-right: 20px;">
    @include('components.confirm-delete-modal', [
        'id' => $manager->id,
        'name' => '',
        'route' => route('managers.destroy', $manager->id)
    ])
</div>
