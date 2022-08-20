<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        # Get data post
        $post = Post::latest()->paginate(5);

        # Return collection of post as a resource
        return new PostResource(true, 'List Data Posts', $post);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'   => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'title'   => 'required',
            'content' => 'required'
        ]);

        # If Error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        # Upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        # Create data post
        $post = Post::create([
            'image'   => $image->hashName(),
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        return new PostResource(true, 'Data created', $post);
    }

    public function show(Post $post)
    {
        return new PostResource(true, 'Data founded', $post);
    }

    public function update(Request $request, Post $post)
    {
        # Validation
        $validator = Validator::make($request->all(), [
            'title'   => 'required',
            'content' => 'required'
        ]);

        # Error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        # check image not empty
        if ($request->hasFile('image')) {
            # Upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            # Delete old image
            Storage::delete(
                'public/posts/' . $post->image
            );

            # Update with new image
            $post->update([
                'image'   => $image->hashName(),
                'title'   => $request->title,
                'content' => $request->content,
            ]);
        } else {
            # Update w/o image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        return new PostResource(true, 'Data updated', $post);
    }

    public function destroy(Post $post)
    {
        # Delete image
        Storage::delete('public/posts/' . $post->image);

        # Delete data
        $post->delete();

        return new PostResource(true, 'Data deleted', null);
    }
}
