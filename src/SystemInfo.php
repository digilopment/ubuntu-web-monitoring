<?php

class SystemInfo
{

    private $data;

    public function __construct()
    {
        $this->getData();
    }

    private function executeCommand($command)
    {
        return trim(shell_exec($command));
    }

    private function parseUptime($uptime)
    {
        $load = explode('load average: ', $uptime);
        $load = explode(', ', $load[1]);

        return array(
            '1min' => $load[0],
            '5min' => $load[1],
            '15min' => $load[2]
        );
    }

    public function getData()
    {
        $uptime = $this->executeCommand('uptime');
        $freeMem = $this->executeCommand('free -m | awk \'/Mem:/ {print $4}\'');
        $cpuTemp = $this->executeCommand('cat /sys/class/thermal/thermal_zone0/temp');
        $diskSpace = $this->executeCommand('df -h / | awk \'NR==2 {print $4}\'');
        $ipAddress = $this->executeCommand('hostname -I');
        $dockerInfo = json_decode($this->executeCommand('docker info --format \'{{json .}}\''), true);
        $dockerContainers = json_decode($this->executeCommand('docker ps --all --no-trunc --format="{{json . }}" | jq --tab -s .'), true);
        $osRelease = $this->executeCommand('cat /etc/os-release | grep PRETTY_NAME | cut -d "=" -f 2-');
        $cpuUsage = $this->executeCommand('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1"%"}\'');
        $memoryUsage = $this->executeCommand('free | awk \'FNR == 2 {printf "%.2f", $3/$2*100}\'');
        $diskUsage = $this->executeCommand('df -h / | awk \'NR==2 {print $5}\'');
        $networkInfo = $this->executeCommand('ifconfig -a | awk \'/UP/ {print $1}\' | sed \'s/.$//\' | xargs -n1 ifconfig | awk \'/RX packets/ {print "device " $1 ", RX: " $5 ", TX: " $9}\'');

        $this->data = array(
            'uptime' => trim($uptime),
            'load_avg' => $this->parseUptime($uptime),
            'free_mem' => $freeMem . 'MB',
            'cpu_temp' => $cpuTemp / 1000 . '°C',
            'disk_space' => $diskSpace,
            'ip_address' => $ipAddress,
            'os_release' => $osRelease,
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage . '%',
            'disk_usage' => $diskUsage,
            'network_info' => $networkInfo,
            'docker_info' => $dockerInfo,
            'docker_containers' => $dockerContainers,
        );

        return $this;
    }

    public function withJson()
    {
        $this->data = json_encode($this->data);
        return $this;
    }

    public function render()
    {
        print($this->data);
    }

}
