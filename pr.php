<?php

require_once "./vendor/autoload.php";

$list = dictOf(["male"=>"kk"]);
$list["mila"] = "oop";
print_r($list->size);
//echo count($list);
//foreach ($list as $index=>$item){
//    echo $index;
//    print_r($item);
//}
