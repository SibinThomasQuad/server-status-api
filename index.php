<?php
class Data
{
    public static function HumanSize($Bytes)
    {
        $Type=array("", "kilo", "mega", "giga", "tera", "peta", "exa", "zetta", "yotta");
        $Index=0;
        while($Bytes>=1024)
        {
            $Bytes/=1024;
            $Index++;
        }
        return("".$Bytes." ".$Type[$Index]."bytes");
    }
}

class Server
{
    public static function average_load()
    {
        return sys_getloadavg();
    }
    public static function memory() 
    {       
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $meminfo[$key] = trim($val);
        }
        return $meminfo;
    }
    
    public static function cpu_core()
    {
        $ncpu = 1;
        if(is_file('/proc/cpuinfo')) 
        {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $ncpu = count($matches[0]);
        }
        return $ncpu;
    }
    
    public static function cpu_usage()
    {
        $stat1 = file('/proc/stat'); 
        sleep(1); 
        $stat2 = file('/proc/stat'); 
        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0])); 
        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0])); 
        $dif = array(); 
        $dif['user'] = $info2[0] - $info1[0]; 
        $dif['nice'] = $info2[1] - $info1[1]; 
        $dif['sys'] = $info2[2] - $info1[2]; 
        $dif['idle'] = $info2[3] - $info1[3]; 
        $total = array_sum($dif); 
        $cpu = array(); 
            foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
        return $cpu;
    }
    
    public static function disk_free()
    {
        $df = disk_free_space("/");
        return Data:: HumanSize($df);
    }
}

class Response
{
    public static function all()
    {
       $server_info = array();
       $server_info["cpu_core"] = Server::cpu_core();
       $server_info["load_verage"] = Server::average_load();
       $server_info["memory_usage"] = Server::memory();
       $server_info["cpu_usage"] = Server::cpu_usage();
       $server_info["disk_space_free"] = Server::disk_free();
       return $server_info;
    }
    public static function api($data)
    {
        echo json_encode($data);
    }
}

class Start
{
    public static function index()
    {
       header('Content-Type: application/json; charset=utf-8');
       if(isset($_GET['data']))
       {
            $data_type = $_GET['data'];
            switch ($data_type) 
            {
                case "all":
                    echo Response::api(Response::all());
                    break;
                case "cpu_core":
                    echo Response::api(Server::cpu_core());
                    break;
                case "memory_usage":
                    echo Response::api(Server::memory());
                    break;
                case "cpu_usage":
                    echo Response::api(Server::cpu_usage());
                    break;
                 case "disk_free":
                    echo Response::api(Server::disk_free());
                    break;
                default:
                    echo Response::api(array('status'=>false,"message"=>'invalid data'));
            }
       }
       else
       {
           echo json_encode(array('status'=>false,"message"=>'please sent a data'));
       }
    }
}

Start::index();
?>
