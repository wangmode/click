<?php
/*------------------公共部分--------------------*/
/*获取PC banner图数组*/
function getBanners($site_info = []){
	$pc_slide = [];
	if(array_key_exists("pc_slide",$site_info)&&!empty($site_info['pc_slide'])){
	$pc_slide = $site_info['pc_slide'];
	}
	return $pc_slide;
}

/*获取手机banner图数组*/
function getMobileBanners($site_info = []){
	$mobile_slide = [];
	if(array_key_exists("mobile_slide",$site_info)&&!empty($site_info['mobile_slide'])){
		$mobile_slide = $site_info['mobile_slide'];
	}
	return $mobile_slide;
}


	//获取当前位置数组
function getPosition($category_id,$position = [],$raposition=[]){

	$cat = think\Db::table('yzt_portal_category')
	->where('id ='.$category_id)
	->find();

	if($cat['parent_id']!=0){

		$position =getPosition($cat['parent_id'],$position,$raposition);

	}
	$position[]=['name' => $cat['name'],'href'=>''];
	return $position;

}


	/*
	* array unique_rand( int $min, int $max, int $num )
	* 生成一定数量的不重复随机数
	* $min 和 $max: 指定随机数的范围
	* $num: 指定生成数量
	*/
	function unique_rand($min, $max, $num) {
		//初始化变量为0
		$count = 0;
		//建一个新数组
		$return = array();
		while ($count < $num) {
		//在一定范围内随机生成一个数放入数组中
		$return[] = mt_rand($min, $max);
		//去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
		$return = array_flip(array_flip($return));
		//将数组的数量存入变量count中
		$count = count($return);
		}
		//为数组赋予新的键名
		shuffle($return);
		return $return;
		}
		//随即获取关键词
		function getKeywords($site_info){
		$keywords = explode(',',$site_info['keywords']);
		$kmax = count($keywords)-1;

		$k = unique_rand(0,$kmax,3);

		$keyword[] = $keywords[$k[0]];
		$keyword[] = $keywords[$k[1]];
		$keyword[] = $keywords[$k[2]];
		return $keyword;
	}

	/**
	*首页常用方法
	*/



	//获得该栏目id下单页栏目的内容
	function getPageByCid($cid = 0){

		$res = think\Db::table('yzt_portal_category_post')
		->where("status=1")
		->where('post_status = 1')
		->where('post_type = 2')
		->alias('a')
		->join('yzt_portal_post b','a.post_id = b.id')
		->find();
		$category = think\Db::table('yzt_portal_category')
		->where(["status"=>1])
		->where(["delete_time"=>0])
		->where(["id"=>$cid])
		->find();
		if(!empty($category)){
			$res = array_merge($res,$category);
		}
		return $res;
	}


	//获得该栏目id下列表栏目下的所有内容
	function getArticles($category_id,$pageset = 10){
		$category = think\Db::table('yzt_portal_category')
		->where(["status"=>1])
		->where(["delete_time"=>0])
		->where(["id"=>$category_id])
		->find();

		if(!empty($category)){
			$cats = getChildrenCid($category_id);
			$where = "(category_id=".$category_id." and post_type=".$category['post_type'].")";
			foreach($cats as $k =>$cat){
				$where =$where." or (category_id=".$cat['id']." and post_type=".$cat['post_type'].")";
			}

			$res = think\Db::table('yzt_portal_category_post')
			->where($where)
			->where("status=1")
			->where('post_status = 1')
			->where('post_type != 2')
			->alias('a')
			->join('yzt_portal_post b','a.post_id = b.id')
			->orderRaw('rand()')
			->paginate($pageset);

			return $res;
		}
	}

	//获得随机文章内容
	function getRandArticles($pageset = 10){
		$res = think\Db::table('yzt_portal_category_post')
		->where("status=1")
		->where('post_status = 1')
		->where('post_type = 1')
		->alias('a')
		->join('yzt_portal_post b','a.post_id = b.id')
		->orderRaw('rand()')
		->paginate($pageset);
		return $res;
	}

	//获得随机文章内容
	function getRandProducts($pageset = 10){
		$res = think\Db::table('yzt_portal_category_post')
			->where("status=1")
			->where('post_status = 1')
			->where('post_type = 3')
			->alias('a')
			->join('yzt_portal_post b','a.post_id = b.id')
			->orderRaw('rand()')
			->paginate($pageset);
		return $res;
	}

	//获得该栏目id下的所有子栏目
	function getChildrenCid($category_id){
		$res = think\Db::table('yzt_portal_category')
		->where(["status"=>1])
		->where(["delete_time"=>0])
		->where(["parent_id"=>$category_id])
		->select();
		return $res;
	}

	//通过栏目查找栏目的具体信息
	function getCategory($id = 1){
		$res = think\Db::table('yzt_portal_category')
		->where(["status"=>1])
		->where(["delete_time"=>0])
		->where(["id"=>$id])
		->find();
		return $res;

	}


//获取栏目及子栏目树形数组，用于导航
	function getCategoryTree($maxnum = 20,$maxLevel = 0){
		$navMenus     = think\Db::name('portal_category')->where('status', 1)
					->where(["status"=>1])
					->where(["delete_time"=>0])
					->order('list_order ASC')
					->select()
					->toArray();


		$tree = new tree\Tree();
		$tree->init($navMenus);
		$navMenusTree = $tree->getTreeArray(0, $maxLevel);
      
     $navMenusTree = array_slice($navMenusTree,0,$maxnum-1);
      

	return $navMenusTree;

	}


	//获取友情链接
	function getLinls($num = 5){
	$res = think\Db::table('yzt_link')
	//->where(["status"=>1])
	->limit($num)
	->select();
	return $res;
	}


	//模糊查询 搜索函数
	function searchArticles($pageset = 10,$keyword =''){
		$where=[
		'create_time'=>['egt',0],
		'post_title'=>['like',"%$keyword%"]
		];
		$res = think\Db::table('yzt_portal_post')
		->alias('a')
		->join('yzt_portal_category_post c','a.id = c.post_id')
		->where($where)
		->paginate($pageset,false,['query' => ['keyword'=>$keyword]]);
		return $res;
	}

	//获取当前站的城市名称
	function getCity(){
		$city = '';
		$pinyin ="";
		$request = \think\Request::instance();
		$baseurl = $request->baseUrl();
		$baseurlarr = explode('/',$baseurl);
		if(!empty($baseurlarr)&&count($baseurlarr)>=2){
			$pinyin = $baseurlarr[1];
			if(!empty($pinyin)){
				$where=[
				'pinyin'=>$pinyin,
				'status'=>1
				];
				$city = think\Db::name('city')
				->where($where)
				->value('name');
				if(empty($city)){
				$city ='';
				}

			}
		}
		return $city;
	}

//获取当前站的城市名称
function getCityPinyin(){

	$url ="";
	$request = \think\Request::instance();
	$baseurl = $request->baseUrl();
	$baseurlarr = explode('/',$baseurl);
	if(!empty($baseurlarr)&&count($baseurlarr)>=2){
		if(!empty($baseurlarr[1])){
			$pinyin = $baseurlarr[1];

			if(!empty($pinyin)){
				$where=[
					'pinyin'=>$pinyin,
					'status'=>1
				];
				$city = think\Db::name('city')
					->where($where)
					->value('name');
				if(!empty($city)){
					$url = '/'.$pinyin;
				}else{
					$url="";
				}

			}


		}

	}
	return $url;
}

// 将文字中的【diqu】标签转化为城市
function parse_diqu($str = ''){
	$city = getCity();
	$str = str_replace('【diqu】',$city,$str);
	return $str;
}

//获取当前栏目的顶级栏目 通过id
function getTopCid($id = 1){

	$res = think\Db::table('yzt_portal_category')
		->where(["id"=>$id])
		->find();
	if(!empty($res)){
		$patharr = explode('-',$res['path']);
		$id = $patharr[1];
	}
	return $id;
}
//截取标题中最后一项，即获取公司名称
function getCompanyName($sitename=""){
  if(!empty($sitename)){
    str_replace([",","-","_"],["|","|","|"],$sitename);
  	  $arr  = explode("|",$sitename);
      if(is_array($arr)&&!empty($arr)){
        $sitename = end($arr);
      }
      
  }
  return $sitename;

}


//分页 
 function getPage($total,$pageset = 1,$currentPage,$baseurl=""){
    $page = [];
    $pageCount = ceil($total/$pageset);//总页数
    
    $prev = $currentPage - 1;//上一页
    $prev = $prev < 1 ? 1 : $prev;
    $next = $currentPage + 1;//下一页
    $next = $next > $pageCount ? $pageCount : $next;
    $pageList = [];
    for($i=1;$i<=$pageCount;$i++){
    	$pageList[]=['page'=>$i,'url'=>$baseurl."?page=".$i];
    }
    
    $page = [
    	'prev'=>['page'=>$prev,'url'=>$baseurl."?page=".$prev],
      	'next'=>['page'=>$next,'url'=>$baseurl."?page=".$next],
      	'currentPage'=>['page'=>$currentPage,'url'=>$baseurl."?page=".$currentPage],
      	'pageList'=>$pageList
    ];
    
  	return $page;
  }


