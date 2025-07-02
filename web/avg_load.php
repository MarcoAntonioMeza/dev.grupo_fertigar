<?php
function isEnabled($func) {
    return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
}

function get_server_memory_usage(){
    if(isEnabled('shell_exec')){
        $free = (string) trim(shell_exec('free'));
        $free_arr = explode("\n", $free);
        $mem = array_filter(explode(" ", $free_arr[1]));
        $mem = array_merge($mem);

        $memTotal      = $mem[1];
        $MemAvailable  = $mem[6];

    }else{
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();

        foreach ($data as $line) {
            if($line){
                list($key, $val) = explode(":", $line);
                $meminfo[$key] = trim($val);
            }
        }

        $memTotal      = $meminfo['MemTotal'];
        $MemAvailable  = $meminfo['MemAvailable'];
    }

    return round((($memTotal - $MemAvailable) / $memTotal) * 100, 1);
}


function get_server_cpu_usage(){
    if (stristr(PHP_OS, 'win')){
        $cpu_num    = 0;
        $load_total = 0;

        $wmi = new COM("Winmgmts://");
        $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");

        foreach($server as $cpu){
            $cpu_num++;
            $load_total += $cpu->loadpercentage;
        }

        return round($load_total / $cpu_num, 1);

    }else{
        $loads = sys_getloadavg();

        $core_nums = (isEnabled('shell_exec'))? trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l")): 1;

        return round($loads[0] / ($core_nums + 1) * 100, 1);
    }
}

echo json_encode(['mem' => get_server_memory_usage(), 'cpu' => get_server_cpu_usage()], true);