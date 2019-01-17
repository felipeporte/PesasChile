{if $enddate!=null && $enddate >0 }
<div class='time_count_down'>
    <span class="future_date_{$id_cate}_{$id_product_time} time_countdown"  data-date-y ='{$enddate|date_format:"%Y"}' data-date-m ='{$enddate|date_format:"%m"}' data-date-d='{$enddate|date_format:"%d"}' data-date-h = '{$enddate|date_format:"%H"}' data-date-mi = '{$enddate|date_format:"%M"}' data-date-s= '{$enddate|date_format:"%S"}' >  </span>
 </div>
{/if}
