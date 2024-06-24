<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function slider_list($position)
    {
        $args = [
            ['position', '=', $position],
            ['status', '=', 1]
        ];
        $sliders = Slider::where($args)
            ->orderBy('sort_order', 'ASC')
            ->get();
        return response()->json(
            [
                'success' => true,
                'message' => 'Tải dữ liệu thành công',
                'sliders' => $sliders
            ],
            200
        );
    }
    public function index()
    {
        $sliders = Slider::orderBy('created_at', 'DESC')->get();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'sliders' => $sliders
            ],
            200
        );
    }
    public function show($id)
    {
        $slider = Slider::find($id);
        return response()->json(
            ['success' => true, 'message' => 'Tải dữ liệu thành công', 'slider' => $slider],
            200
        );
    }
    public function store(Request $request)
    {
        $slider = new slider();
        $slider->name = $request->name; //form
        $slider->link = $request->link; //form
        $slider->sort_order = $request->sort_order; //form
        $slider->position = $request->position; //form
        $slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = $slug . '.' . $extension;
                $slider->image = $filename;
                $files->move(public_path('images/slider'), $filename);
            }
        }
        $slider->created_at = date('Y-m-d H:i:s');
        $slider->created_by = 1;
        $slider->status = $request->status; //form
        $slider->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'slider' => $slider
            ],
            201
        );
    }
    public function update(Request $request, $id)
    {
        $slider = Slider::find($id);
        $slider->name = $request->name; //form
        $slider->link = $request->link; //form
        $slider->sort_order = $request->sort_order; //form
        $slider->position = $request->position; //form
        $slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = $slug . '.' . $extension;
                $slider->image = $filename;
                $files->move(public_path('images/slider'), $filename);
            }
        }
        $slider->updated_at = date('Y-m-d H:i:s');
        $slider->updated_by = 1;
        $slider->status = $request->status; //form
        $slider->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'slider' => $slider
            ],
            200
        );
    }
    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Xóa thành công',
                'slider' => null
            ],
            200
        );
    }

}
