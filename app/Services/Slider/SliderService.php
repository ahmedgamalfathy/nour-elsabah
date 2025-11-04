<?php
namespace App\Services\Slider;

use App\Enums\IsActive;
use App\Models\Slider\Slider;
use App\Models\SliderItem\SliderItem;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SliderService {
    public $sliderItemService;
    public function __construct(SliderItemService $sliderItemService)
    {
        $this->sliderItemService =$sliderItemService;
    }
public function allSlider()
{
 return QueryBuilder::for(Slider::class)
        ->allowedFilters(['name',
        AllowedFilter::exact('isActive', 'is_active')
        ])->get();
}
public function editSlider(int $id)
{
   $slider= Slider::with('products')->find($id);
   if(!$slider){
    throw new ModelNotFoundException();
   }
    return $slider;
}
public function createSlider(array $data)
{
    $slider =Slider::create([
    'name'=>$data['name'],
    'is_active'=>IsActive::from($data['isActive'])->value,
    'start_date'=>$data['startDate'] ?? null,
    'end_date'=>$data['endDate'] ?? null
    ]);
    $slider->products()->attach($data['sliderItems']);
    if ($data['isActive'] == 1) {
        Slider::where('id', '!=', $slider->id)
        ->where('is_active', 1)
        ->update(['is_active' => 0]);
    }

    return $slider;
}
public function updateSlider(int $id, array $data)
{
   $slider= Slider::find($id);
   if(!$slider){
    throw new ModelNotFoundException();
   }
   $slider->name = $data['name'];
   $slider->is_active =IsActive::from($data['isActive'])->value;
   $slider->start_date =$data['startDate'] ?? null;
   $slider->end_date =$data['endDate'] ?? null;
   $slider->save();
   $slider->products()->sync($data['sliderItems']);
   if ($data['isActive'] == 1) {
    Slider::where('id', '!=', $slider->id)
    ->where('is_active', 1)
    ->update(['is_active' => 0]);
}
}
public function deleteSlider(int $id)
{
 $slider =Slider::find($id);
 if(!$slider){
    throw new ModelNotFoundException();
 }
 $slider->delete();

}
public function changeSliderStatus(int $id,int $isActive)
{
    $slider = Slider::find($id);
    if(!$slider){
        throw new ModelNotFoundException();
    }
    $slider->is_active =IsActive::from($isActive)->value;
    $slider->save();
    if ($isActive == 1) {
        Slider::where('id', '!=', $slider->id)
        ->where('is_active', 1)
        ->update(['is_active' => 0]);
    }

}

}
