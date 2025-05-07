@include('components.confirm-delete-modal', [
    'id' => $manager->id,
    'route' => route('managers.destroy', $manager->id)
])
