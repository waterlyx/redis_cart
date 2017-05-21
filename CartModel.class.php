<?php
/**
 * User: 刘业兴
 * Date: 2017/4/12
 * Time: 16:07
 * 描述：购物车模型层
 */
namespace Common\Model;
class CartModel extends CommonModel{

    //不去检测数据库字段
    Protected $autoCheckFields = false;

    /**
     * User: 刘业兴
     * @param $userid 用户id
     * @param $goods_id 商品的id
     * @param $goods_number 商品的数量
     * @return bool 状态
     * 描述：添加商品进入购物车
     */
    public function addCart($userid,$goods_id,$goods_number){
        //拼接用户id
        $user = 'cart_'.$userid;
        //如果存在用户的购物车数据
        if($data = redis() -> get($user)){
            //将存储在redis的数据转成数组
            $data = json_decode($data,true);
            $data[$goods_id] += $goods_number;
        }else{
            $data[$goods_id] = $goods_number;
        }
        //将数据插入购物车
        $value = json_encode($data);
        //插入成功
        if(redis()-> set($user,$value)){
            return true;
        }else{
            //插入失败
            return false;
        }
    }

    /**
     * User: 刘业兴
     * @param $userid 用户的id
     * @param $goods_id 商品的id
     * @return bool 状态
     * 描述：删除指定商品
     */
    public function delCart($userid,$goods_id){
        //拼接用户id
        $user = 'cart_'.$userid;
        if ($data = redis()->get($user)){
            //将存储在redis的数据转成数组
            $data = json_decode($data,true);
            unset($data[$goods_id]);
            $value = json_encode($data);
            if(redis()-> set($user,$value)){
                //删除指定商品成功
                return true;
            }else{
                //删除之后数据无法更新
                return false;
            }
        }else{
            //未找到该商品
            return false;
        }
    }

    /**
     * User: 刘业兴
     * @param $userid 用户的id
     * @return bool 状态
     * 描述：清空购物车
     */
    public function clearCart($userid){
        //拼接用户id
        $user = 'cart_'.$userid;
        if(redis() -> del($user)){
            //清空成功
            return true;
        }else{
            //清空失败
            return false;
        }
    }

    /**
     * User: 刘业兴
     * @param $userid 用户的id
     * @return array|bool 正确返回二维数组，错误返回false
     * 描述：展示购物车数据
     */
    public function showCart($userid){
        //拼接用户id
        $user = 'cart_'.$userid;
        $res = redis() -> get($user);
        if($res != "[]" && !empty($res)){
            //将返回的json信息转成数组
            $res = json_decode($res,true);
            //构建购物车商品的数组
            $data = array();
            foreach ($res as $key => $value) {
                $data[$key]["goods_id"] = (string)$key;
                $data[$key]["goods_buy_number"] = (string)(int)$value;
                //构建商品id的数组，降低数据库压力
                $keys[] = $key;
            }
            ksort($data);
            //构建商品的字符串进行数据库查询
            $key_str = implode(',',$keys);
            //进行mysql数据库的查找
            $prices = M('Goods')
                -> alias('g')
                -> join('ac_goods_category as gc on gc.gc_id = g.gc_id')
                -> field('goods_id,goods_name,goods_price,goods_thumb,gc_name')
                -> where("`goods_id` in ($key_str)")
                -> select();
            $n = 0;
            foreach($data as $key => $value){
                $data[$key]['goods_name'] = $prices[$n]["goods_name"];
                $data[$key]["goods_price"] = $prices[$n]["goods_price"];
                $data[$key]["goods_total_price"] = (string)($prices[$n]["goods_price"]*$data[$key]["goods_buy_number"]);
                $data[$key]["goods_thumb"] = (string)(C('FILE_ROOT').$prices[$n]["goods_thumb"]);
                $data[$key]["gc_name"] = (string)($prices[$n]["gc_name"]);
                $n++;
            }
            return $data;
        }else{
            return false;
        }
    }

    /**
     * User: 刘业兴
     * @param $userid 用户的id
     * @param $goods_id 商品的id
     * @param $num 商品的数量
     * @return bool 状态
     * 描述：购物车数量改变
     */
    public function numberCart($userid,$goods_id,$num){
        //拼接用户id
        $user = 'cart_'.$userid;
        //如果存在用户的购物车数据
        if ($data =  redis()->get($user)){
            //将存储在redis的数据转成数组
            $data = json_decode($data,true);
            $data[$goods_id] = $num;
        }else{
            $data[$goods_id] = $num;
        }
        //将数据插入购物车
        $value = json_encode($data);
        if(redis()-> set($user,$value)){
            return true;
        }else{
            return false;
        }
    }
}