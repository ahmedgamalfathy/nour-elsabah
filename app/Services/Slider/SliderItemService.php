<?php
namespace App\Services\Slider;

use App\Models\SliderItem\SliderItem;
use Spatie\QueryBuilder\QueryBuilder;

class SliderItemService {

public function editSlider()
{

}
public function createSlider(array $data)
{
  return  SliderItem::create([
          'product_id'=>$data['productId'],
          'slider_id'=>$data['sliderId']
    ]);


}

public function deleteSlider(int $id)
{
  $sliderItem=SliderItem::find($id);
  $sliderItem->delete();
}

}
