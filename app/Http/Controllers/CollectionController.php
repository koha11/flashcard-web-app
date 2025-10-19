<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::query()
            ->when($request->filled('owner_id'), fn($q) => $q->where('owner_id', $request->integer('owner_id')))
            ->when(
                $request->filled('access_level'),
                fn($q) =>
                $q->where('access_level', $request->string('access_level')->toString())
            );

        return CollectionResource::collection($query->latest()->paginate());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'access_level' => ['sometimes', Rule::in(['private', 'public', 'restrict'])],
        ]);

        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id ?? null;
        $collection = Collection::create($data);

        return new CollectionResource($collection);
    }

    public function show(Collection $collection)
    {
        return new CollectionResource($collection);
    }

    public function update(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'access_level' => ['sometimes', 'required', Rule::in(['private', 'public', 'restrict'])],
            'owner_id' => ['prohibited'], // avoid changing ownership via API
        ]);

        $collection->update($data);

        return new CollectionResource($collection->fresh());
    }

    public function destroy(Collection $collection)
    {
        $collection->delete(); // hard delete (no softDeletes on this table)
        return response()->noContent();
    }
}
