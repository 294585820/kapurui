<?php
/**
 * Created by PhpStorm.
 * User: songjieliu
 * Date: 2022/4/29
 * Time: 1:45 PM
 */
//header("content-type: UTF-8");
/***
 * 亚马逊正则获取页面详细信息
 */
function amazonPreg($content) {
    //评论时间
    $pattern = '/review-date">([^>]+)>/iUs';
    preg_match_all($pattern, $content, $matches);
    //具体时间
    $pattern = '/on([^<]+)</iUs';
    preg_match_all($pattern, $matches[1][0], $date);
    $data['date'] = $date[1][0];
    //星星
    $pattern = '/a-icon-star a-star-(\d)/iUs';
    preg_match_all($pattern, $content, $star);
    $data['star'] = $star[1][0];
    //用户
    $pattern = '/a-profile-name">([^<]+)</iUs';
    preg_match_all($pattern, $content, $username);
    $data['username'] = $username[1][0];
    //内容
    $pattern = '/review-body.*span>([^<]+)</iUs';
    preg_match_all($pattern, $content, $info);
    $data['content'] = $info[1][0];
    return $data;
}
/**
 *  获取抓取页面信息
 */
function getContentHtml($url, &$result) {
    //内容
    $content = file_get_contents($url);
    $pattern = '/data-hook\="review".*review-comments/iUs';
    preg_match_all($pattern, $content, $matches);
    foreach ($matches[0] as $key => $val){
        $result[] = amazonPreg($val);
    }
    //下一页
    $pageNums = substr($url, -1, 1);
    if(intval($pageNums) >= 2) return false;
    $pattern = '/class="a-last".*href\="([^"]+)">/iUs';
    preg_match_all($pattern, $content, $page);
    $urlNextPage = 'https://www.amazon.com' . $page[1][0];
    $urlNextPage = htmlspecialchars_decode($urlNextPage);
    getContentHtml($urlNextPage, $result);
}
/*
 * 导出Csv
 * */
function exportCsv($result) {
    //评论日期，评分，评论用户，评论内容
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="1.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $file = fopen('php://output', 'w');
    fputcsv($file, array('评论日期', '评分', '评论用户', '评论内容'));
    foreach ($result as $row)
    {
        fputcsv($file, $row);
    }
    exit;
}

$url = 'https://www.amazon.com/product-reviews/B09DG4DLYZ';
$result = [];
getContentHtml($url, $result);
exportCsv($result);