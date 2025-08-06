<?php
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------


use think\facade\Db;
use think\facade\Log;
use think\facade\Config;
use think\exception\ValidateException;


error_reporting(0);


/**
 * 随机字符
 * @param int $length 长度
 * @param string $type 类型
 * @param int $convert 转换大小写 1大写 0小写
 * @return string
 */
function random($length=10, $type='letter', $convert=0)
{
    $config = array(
        'number'=>'1234567890',
        'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if(!isset($config[$type])) $type = 'letter';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) -1;
    for($i = 0; $i < $length; $i++){
        $code .= $string[mt_rand(0, $strlen)];
    }
    if(!empty($convert)){
        $code = ($convert > 0)? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/*
 * 生成交易流水号
 * @param char(2) $type
 */
function doOrderSn($type){
	return date('YmdHis') .$type. substr(microtime(), 2, 3) .  sprintf('%02d', rand(0, 99));
}


//后台sql输入框语句过滤
function sql_replace($str){
	$farr = ["/insert[\s]+|update[\s]+|create[\s]+|alter[\s]+|delete[\s]+|drop[\s]+|load_file|outfile|dump/is"];
	$str = preg_replace($farr,'',$str);
	return $str;
}

//上传文件黑名单过滤
function upload_replace($str){
	$farr = ["/php|php3|php4|php5|phtml|pht|/is"];
	$str = preg_replace($farr,'',$str);
	return $str;
}

//查询方法过滤
function serach_in($str){
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    $farr = ["/select/i", "/insert/i", "/and/i", "/or/i", "/create/i", "/update/i", "/delete/i", "/alter/i", "/count/i", "/union/i", "/load_file/i", "/outfile/i"];
    $str = preg_replace($farr, '', $str);
    return trim($str);
}

//获取键值对信息
function getItemData($data){
	$str = in_array(json_encode(array_values($data)),['[]','[[]]']) ? '' : json_encode(array_values($data),320);
	return $str;
}


/*获取应用url前缀*/
function getBaseUrl(){
	$baseAppName = app('http')->getName();
	if(config('app.app_map')){
		$newapp = array_flip(config('app.app_map'))[$baseAppName];
		if($newapp) $baseAppName = $newapp;
	}

	$basename ='/'.$baseAppName;

	if(config('app.domain_bind')){
		$newapp = array_flip(config('app.domain_bind'))[$baseAppName];
		if($newapp) $basename = '';
	}

	return $basename;
}


//无限极分类转为带有 children的树形list表格结构
if (!function_exists('_generateListTree')) {
	function _generateListTree ($data, $pid = 0,$config=[]) {
		$tree = [];
		if ($data && is_array($data)) {
			foreach($data as $v) {
				if($v[$config[1]] == $pid) {
					$tree[] = array_merge($v,['children' => _generateListTree($data, $v[$config[0]],$config)]);
				}
			}
		}
		return $tree;
	}
}

/**
 * 实例化数据库类
 * @param string        $name 操作的数据表名称（不含前缀）
 * @param array|string  $config 数据库配置参数
 * @param bool          $force 是否强制重新连接
 * @return \think\db\Query
 */
if (!function_exists('db')) {
    function db($name = '',$connect='')
    {
		if(empty($connect)){
			$connect = config('database.default');
		}
        return Db::connect($connect,false)->name($name);
    }
}

//钩子函数
function hook($hookname,&$data){
	$path = str_replace('/', '\\',$hookname);
	list($controller,$action) = explode('@',$path);
	$controller = app($controller);
	if(method_exists($controller, $action)) {
		try{
			$response = call_user_func_array([$controller, $action], [&$data]);
		}catch(\Exception $e){
			throw new ValidateException($e->getMessage());
		}

		return $response;
	}
}


/**
 * 计算（周）相关
 *
 * @param int $startTimestamp 开始时间戳
 * @param int $endTimestamp 结束时间戳
 * @param int $weekNumber 查第几周
 * @param string $cycle 是否使用当天的第几周
 * @return array 结果
 */
function calculateNaturalWeeks($startTimestamp, $endTimestamp, $weekNumber, $cycle='') {

    // 创建日期对象
    $start = new \DateTime();
    $start->setTimestamp($startTimestamp);

    $end = new \DateTime();
    $end->setTimestamp($endTimestamp);

    // 获取开始日期所在的周的第一天（周一）
    $startWeek = clone $start;
    $startWeek->modify('Monday this week');

    // 获取结束日期所在的周的最后一天（周日）
    $endWeek = clone $end;
    $endWeek->modify('Sunday this week');

    // 计算两个周之间的天数差
    $diff = $startWeek->diff($endWeek);
    $days = $diff->days;

    // 计算跨越的周数（天数除以7再加1）
    $weeks = (int)(($days / 7) + 1);

    $publishMonday = strtotime('last monday', $startTimestamp + 86400); // +86400确保当天是周一也能正确处理
    $publishSunday = strtotime('sunday', $startTimestamp);
    $today = time();
    if ($today < $publishMonday) {
        $current_week_index = 0;
    } else {
        $daysDiff = floor(($today - $publishMonday) / 86400);
        $current_week_index = (int)floor($daysDiff / 7) + 1;
    }
    if ($cycle !== 'cycle') {
        $weekNumber = $current_week_index;
    }

    // 计算第一周（发布时间到本周日）
    if ($weekNumber == 1) {
        $firstWeekEnd = strtotime('next sunday', $startTimestamp) + 86399;
        // 如果发布日期本身就是周日
        if (date('w', $startTimestamp) == 0) {
            $firstWeekEnd = $startTimestamp + 86399;
        }

        $cycles = [];
        for ($i = 1; $i <= $weeks; $i++) {
            $cycles[] = $i;
        }
        $cycle_zg = [];
        return [
            'cycle_index' => $weekNumber,
            'cycles' => $cycles,
            'cycle_zg' => $cycle_zg,
            'cycle_start' => $startTimestamp,
            'cycle_end' => min($firstWeekEnd, $endTimestamp),
            'cycle_start_date' => date('Y-m-d', $startTimestamp),
            'cycle_end_date' => date('Y-m-d', min($firstWeekEnd, $endTimestamp))
        ];
    }

    // 对于第2周及以后，从第一周的下周一开始计算
    $firstWeekEnd = strtotime('next sunday', $startTimestamp) + 86399;
    $secondWeekStart = strtotime('next monday', $startTimestamp);

    // 计算第N周的周一
    $targetMonday = strtotime('+' . ($weekNumber - 2) . ' weeks', $secondWeekStart);

    // 计算这周的周日 (23:59:59)
    $targetSunday = strtotime('next sunday', $targetMonday) + 86399;

    $targetSunday = min($targetSunday, $endTimestamp);

    $cycles = [];
    for ($i = 1; $i <= $weeks; $i++) {
        $cycles[] = $i;
    }

    $cycle_zg = [];
    return [
        'cycle_index' => $weekNumber,
        'cycles' => $cycles,
        'cycle_zg' => $cycle_zg,
        'cycle_start' => $targetMonday,
        'cycle_end' => $targetSunday,
        'cycle_start_date' => date('Y-m-d', $targetMonday),
        'cycle_end_date' => date('Y-m-d', $targetSunday)
    ];

}


/**
 * 计算（月）相关
 *
 * @param int $startTimestamp 开始时间戳
 * @param int $endTimestamp 结束时间戳
 * @param int $weekNumber 查第几月
 * @param string $cycle 是否使用当天的第几月
 * @return array 结果
 */
function calculateNaturalMonths($startTimestamp, $endTimestamp, $monthIndex, $cycle='') {

    // 获取当前时间戳
    $currentTimestamp = time();

    // 将时间戳转换为DateTime对象
    $publishDateTime = (new \DateTime())->setTimestamp($startTimestamp);
    $currentDateTime = (new \DateTime())->setTimestamp($currentTimestamp);

    // 计算两个日期之间的月份差异
    $interval = $publishDateTime->diff($currentDateTime);
    $monthsDiff = $interval->y * 12 + $interval->m;

    // 如果当前日期在发布日期的日期之后，或者刚好是同一天，需要加1（因为第一个月从发布日当月算起）
    if ($currentDateTime >= $publishDateTime) {
        $monthsDiff += 1;
    }

    $monthsDiff = (int)$monthsDiff;

    if ($cycle !== 'cycle') {
        $monthIndex = $monthsDiff;
    }

    $start = new \DateTime();
    $start->setTimestamp($startTimestamp);

    $end = new \DateTime();
    $end->setTimestamp($endTimestamp);

    $startYear = (int)$start->format('Y');
    $startMonth = (int)$start->format('m');

    $endYear = (int)$end->format('Y');
    $endMonth = (int)$end->format('m');

    // 计算基础月份差
    $months = ($endYear - $startYear) * 12 + ($endMonth - $startMonth);

    // 总是加1，因为即使是在同一个月内，也跨越了1个自然月
    $months = $months + 1;

    // 获取时间范围内的所有自然月
    $monthArr = [];
    $current = $startTimestamp;

    while ($current <= $endTimestamp) {
        $year = date('Y', $current);
        $month = date('m', $current);

        // 计算该月的第一天和最后一天
        $firstDay = strtotime("{$year}-{$month}-01");
        $lastDay = strtotime("last day of {$year}-{$month}", $firstDay);

        // 调整实际开始和结束时间（不超过输入的范围）
        $actualStart = max($firstDay, $startTimestamp);
        $actualEnd = min($lastDay, $endTimestamp);

        // 只有当该月确实在范围内时才添加
        if ($actualStart <= $actualEnd) {
            $monthArr[] = [
                'start' => $actualStart,
                'end' => $actualEnd,
                'month' => "{$year}-{$month}"
            ];
        }

        // 跳到下个月的第一天
        $current = strtotime('+1 month', $firstDay);
    }

    // 返回第N个月的信息（索引从0开始）
    $result = $monthArr[$monthIndex - 1];

    $cycles = [];
    for ($i = 1; $i <= $months; $i++) {
        $cycles[] = $i;
    }
    $cycle_zg = [];
    return [
        'cycle_index' => $monthIndex,
        'cycles' => $cycles,
        'cycle_zg' => $cycle_zg,
        'cycle_start' => $result['start'],
        'cycle_end' => $result['end'],
        'month' => $result['month'],
        'cycle_start_date' => date('Y-m-d H:i:s', $result['start']),
        'cycle_end_date' => date('Y-m-d H:i:s', $result['end'])
    ];

}


/**
 * 计算（天）相关
 *
 * @param int $startTimestamp 开始时间戳
 * @param int $endTimestamp 结束时间戳
 * @param int $dayIndex
 * @param string $cycle
 * @return array 结果
 */
function calculateNaturalDays($startTimestamp, $endTimestamp, $dayIndex, $cycle='') {
    // 创建日期对象并重置时间为00:00:00
    $start = new \DateTime();
    $start->setTimestamp($startTimestamp);
    $start->setTime(0, 0, 0);

    $end = new \DateTime();
    $end->setTimestamp($endTimestamp);
    $end->setTime(0, 0, 0);
    // 计算天数差异
    $diff = $start->diff($end);
    $days = $diff->days;
    // 加上起始当天
    $days = $days + 1;  // 算出有多少天

    // 获取当前时间的时间戳
    $now = time();
    // 计算时间差（秒数）
    $diff = $now - strtotime(date('Y-m-d',$startTimestamp));
    // 将秒数转换为天数并向上取整
    $days_now = ceil($diff / (60 * 60 * 24));
    // 确保最小值为1（当天算第1天）
    $daysPassed = max(1, $days_now);   // 今天是交办的第几天
    if ($cycle !== 'cycle') {
        $dayIndex = (int)$daysPassed;
    }

    // 创建DateTime对象
    $startDate = (new \DateTime())->setTimestamp($startTimestamp);
    $endDate = (new \DateTime())->setTimestamp($endTimestamp);

    // 计算时间范围内的总天数
    $interval = $startDate->diff($endDate);
    $totalDays = $interval->days + 1; // 包含开始和结束当天

    // 计算指定自然日的日期
    $targetDate = clone $startDate;
    $targetDate->add(new \DateInterval('P'.($dayIndex-1).'D'));

    // 计算该自然日的起始和结束时间戳
    $dayStart = (new \DateTime($targetDate->format('Y-m-d')))->setTime(0, 0, 0);
    $dayEnd = (new \DateTime($targetDate->format('Y-m-d')))->setTime(23, 59, 59);

    // 确保不超出原始时间范围
    if ($dayStart->getTimestamp() < $startTimestamp) {
        $dayStart = clone $startDate;
    }

    if ($dayEnd->getTimestamp() > $endTimestamp) {
        $dayEnd = clone $endDate;
    }

    $cycles = [];
    $cycle_zg = [];
    for ($i = 1; $i <= $days; $i++) {
        $cycles[] = $i;
        $cycle_zg[] = date('m-d',$startTimestamp + 86400*($i-1));
    }

    return [
        'cycle_index' => $dayIndex,
        'cycles' => $cycles,
        'cycle_zg' => $cycle_zg,
        'cycle_start' => $dayStart->getTimestamp(),
        'cycle_end' => $dayEnd->getTimestamp(),
        'cycle_start_date' => $dayStart->format('Y-m-d H:i:s'),
        'cycle_end_date' => $dayEnd->format('Y-m-d H:i:s'),
    ];
}



/**
 * 计算（季度）相关
 *
 * @param int $startTimestamp 开始时间戳
 * @param int $endTimestamp 结束时间戳
 * @param int $quarterIndex 季度索引
 * @param string $cycle
 * @return array 结果
 */
function calculateNaturalQuarters($startTimestamp, $endTimestamp, $quarterIndex, $cycle='') {
    // 创建日期对象
    $start = new \DateTime();
    $start->setTimestamp($startTimestamp);

    $end = new \DateTime();
    $end->setTimestamp($endTimestamp);

    // 计算总季度数
    $startQuarter = ceil($start->format('m') / 3);
    $endQuarter = ceil($end->format('m') / 3);
    $startYear = $start->format('Y');
    $endYear = $end->format('Y');

    $totalQuarters = ($endYear - $startYear) * 4 + ($endQuarter - $startQuarter) + 1;

    // 计算当前是第几个季度
    $now = new \DateTime();
    $currentQuarter = ceil($now->format('m') / 3);
    $currentYear = $now->format('Y');

    $quartersPassed = ($currentYear - $startYear) * 4 + ($currentQuarter - $startQuarter) + 1;
    $quartersPassed = max(1, $quartersPassed); // 确保最小值为1

    if ($cycle !== 'cycle') {
        $quarterIndex = (int)$quartersPassed;
    }

    // 计算指定季度的起始和结束日期
    $targetYear = $startYear;
    $targetQuarter = $startQuarter + $quarterIndex - 1;

    // 处理跨年情况
    while ($targetQuarter > 4) {
        $targetQuarter -= 4;
        $targetYear++;
    }

    $quarterStartMonth = ($targetQuarter - 1) * 3 + 1;
    $quarterEndMonth = $targetQuarter * 3;

    $quarterStartDate = new \DateTime();
    $quarterStartDate->setDate($targetYear, $quarterStartMonth, 1);
    $quarterStartDate->setTime(0, 0, 0);

    $quarterEndDate = new \DateTime();
    $quarterEndDate->setDate($targetYear, $quarterEndMonth, 1);
    $quarterEndDate->modify('last day of this month');
    $quarterEndDate->setTime(23, 59, 59);

    // 确保不超出原始时间范围
    if ($quarterStartDate->getTimestamp() < $startTimestamp) {
        $quarterStartDate = clone $start;
    }

    if ($quarterEndDate->getTimestamp() > $endTimestamp) {
        $quarterEndDate = clone $end;
    }
    $totalQuarters = (int)$totalQuarters;
    $cycles = [];
    for ($i = 1; $i <= $totalQuarters; $i++) {
        $cycles[] = $i;
    }
    $cycle_zg = [];
    return [
        'cycle_index' => $quarterIndex,
        'cycles' => $cycles,
        'cycle_zg' => $cycle_zg,
        'cycle_start' => $quarterStartDate->getTimestamp(),
        'cycle_end' => $quarterEndDate->getTimestamp(),
        'cycle_start_date' => $quarterStartDate->format('Y-m-d H:i:s'),
        'cycle_end_date' => $quarterEndDate->format('Y-m-d H:i:s'),
    ];
}


/**
 * 计算（年）相关
 *
 * @param int $startTimestamp 开始时间戳
 * @param int $endTimestamp 结束时间戳
 * @param int $yearIndex
 * @param string $cycle
 * @return array 结果
 */
function calculateNaturalYears($startTimestamp, $endTimestamp, $yearIndex, $cycle='') {
    // 创建日期对象并重置时间为当年1月1日00:00:00
    $start = new \DateTime();
    $start->setTimestamp($startTimestamp);
    $start->setDate($start->format('Y'), 1, 1);
    $start->setTime(0, 0, 0);

    $end = new \DateTime();
    $end->setTimestamp($endTimestamp);
    $end->setDate($end->format('Y'), 1, 1);
    $end->setTime(0, 0, 0);

    // 计算年份差异
    $diff = $start->diff($end);
    $years = $diff->y;
    // 加上起始当年
    $years = $years + 1;  // 算出有多少年

    // 获取当前时间的时间戳
    $now = time();
    // 计算时间差（秒数）
    $diff = $now - $startTimestamp;
    // 将秒数转换为年数并向上取整
    $years_now = ceil($diff / (60 * 60 * 24 * 365));
    // 确保最小值为1（当年算第1年）
    $yearsPassed = max(1, $years_now);   // 现在是交办的第几年
    if ($cycle !== 'cycle') {
        $yearIndex = (int)$yearsPassed;
    }

    // 创建DateTime对象
    $startDate = (new \DateTime())->setTimestamp($startTimestamp);
    $endDate = (new \DateTime())->setTimestamp($endTimestamp);

    // 计算时间范围内的总年数
    $interval = $startDate->diff($endDate);
    $totalYears = $interval->y + 1; // 包含开始和结束当年

    // 计算指定自然年的日期
    $targetYear = clone $startDate;
    $targetYear->add(new \DateInterval('P'.($yearIndex-1).'Y'));

    // 计算该自然年的起始和结束时间戳
    $yearStart = (new \DateTime())->setDate($targetYear->format('Y'), 1, 1)->setTime(0, 0, 0);
    $yearEnd = (new \DateTime())->setDate($targetYear->format('Y'), 12, 31)->setTime(23, 59, 59);

    // 确保不超出原始时间范围
    if ($yearStart->getTimestamp() < $startTimestamp) {
        $yearStart = clone $startDate;
    }

    if ($yearEnd->getTimestamp() > $endTimestamp) {
        $yearEnd = clone $endDate;
    }

    $cycles = [];
    for ($i = 1; $i <= $years; $i++) {
        $cycles[] = $i;
    }
    $cycle_zg = [];
    return [
        'cycle_index' => $yearIndex,
        'cycles' => $cycles,
        'cycle_zg' => $cycle_zg,
        'cycle_start' => $yearStart->getTimestamp(),
        'cycle_end' => $yearEnd->getTimestamp(),
        'cycle_start_date' => $yearStart->format('Y-m-d H:i:s'),
        'cycle_end_date' => $yearEnd->format('Y-m-d H:i:s'),
    ];
}


/**
 * 计算（固定日期）相关
 *
 * @param int $startTimestamp 开始时间戳
 * @param int $endTimestamp 结束时间戳
 * @param int $yearIndex
 * @param string $cycle
 * @return array 结果
 */
function calculatefixedDate($startTimestamp, $endTimestamp) {
    return [
        'cycle_index' => 1,
        'cycles' => [1],
        'cycle_zg' => [date('Y-m-d',$startTimestamp).'~'.date('Y-m-d',$endTimestamp)],
        'cycle_start' => $startTimestamp,
        'cycle_end' => $endTimestamp,
        'cycle_start_date' => date('Y-m-d H:i:s',$startTimestamp),
        'cycle_end_date' => date('Y-m-d H:i:s',$endTimestamp),
    ];
}
