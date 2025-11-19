<?php

namespace App\Http\Controllers;

use App\Models\LetterPair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LetterPairController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'letter_name' => 'required|string|max:50',
            'outline_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'complete_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'difficulty_level' => 'integer|min:1|max:5'
        ]);

        $outlineFile = $request->file('outline_image');
        $completeFile = $request->file('complete_image');
        
        $outlineName = Str::uuid() . '.' . $outlineFile->getClientOriginalExtension();
        $completeName = Str::uuid() . '.' . $completeFile->getClientOriginalExtension();
        
        $outlineFile->move(public_path('uploads/letter_pairs'), $outlineName);
        $completeFile->move(public_path('uploads/letter_pairs'), $completeName);

        $letterPair = LetterPair::create([
            'letter_name' => $request->letter_name,
            'outline_image' => $outlineName,
            'complete_image' => $completeName,
            'difficulty_level' => $request->difficulty_level ?? 1
        ]);

        return response()->json([
            'message' => 'Letter pair created successfully',
            'letter_pair' => $letterPair
        ], 201);
    }

    public function index(Request $request)
    {
        $query = LetterPair::where('is_active', true);
        
        if ($request->difficulty_level) {
            $query->where('difficulty_level', $request->difficulty_level);
        }
        
        $letterPairs = $query->get()->map(function ($pair) {
            return [
                'id' => $pair->id,
                'letter_name' => $pair->letter_name,
                'difficulty_level' => $pair->difficulty_level,
                'outline_url' => $pair->outline_url,
                'complete_url' => $pair->complete_url
            ];
        });

        return response()->json($letterPairs);
    }

    public function getOutlineImage($id)
    {
        $letterPair = LetterPair::findOrFail($id);
        $path = public_path('uploads/letter_pairs/' . $letterPair->outline_image);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path);
    }

    public function getCompleteImage($id)
    {
        $letterPair = LetterPair::findOrFail($id);
        $path = public_path('uploads/letter_pairs/' . $letterPair->complete_image);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path);
    }

    public function destroy($id)
    {
        $letterPair = LetterPair::findOrFail($id);
        $letterPair->is_active = false;
        $letterPair->save();

        return response()->json(['message' => 'Letter pair deactivated']);
    }
}