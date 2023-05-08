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
        $freeMem = $this->executeCommand('free -m | grep "Mem:" | awk \'{print $4}\'');
        $totalRam = round($this->executeCommand('free -t | grep "Mem:" | awk \'{print $2}\'') / 1024, 2);
        $totalSwap = round($this->executeCommand('free -m | grep "Swap:" | awk \'{print $2}\''), 2);
        $freeSwap = round($this->executeCommand('free -m | grep "Swap:" | awk \'{print $4}\''), 2);
        $cpuTemp = $this->executeCommand('cat /sys/class/thermal/thermal_zone0/temp');
        $totalDiskSpace = round($this->executeCommand('df --output=size --total | awk \'END {print $1}\'') / 1024 / 1024, 2);
        $freeDiskSpace = round($this->executeCommand('df --output=avail --total | awk \'END {print $1}\'') / 1024 / 1024, 2);
        $usedDiskSpace = round($this->executeCommand('df --output=used --total | awk \'END {print $1}\'') / 1024 / 1024, 2);
        $ipAddress = $this->executeCommand('hostname -I');
        $dockerInfo = json_decode($this->executeCommand('docker info --format \'{{json .}}\''), true);
        $dockerContainers = json_decode($this->executeCommand('docker ps --all --no-trunc --format="{{json . }}" | jq --tab -s .'), true);
        $osRelease = $this->executeCommand('cat /etc/os-release | grep PRETTY_NAME | cut -d "=" -f 2-');
        $cpuUsage = $this->executeCommand('top -bn1 | grep "Cpu(s)" | awk \'{print $2 + $4}\'');
        $memoryUsage = $this->executeCommand('free | awk \'FNR == 2 {printf "%.2f", $3/$2*100}\'');
        $diskUsage = $this->executeCommand('df -h / | awk \'NR==2 {print $5}\'');
        $macAddr = $this->executeCommand("ip addr show $(awk 'NR==3{print $1}' /proc/net/wireless | tr -d :) | awk '/ether/{print $2}'");
        $totalCores = $this->executeCommand('nproc');

        $this->data = array(
            'uptime' => trim($uptime),
            'load_avg' => $this->parseUptime($uptime),
            'free_mem' => $freeMem . 'MB',
            'total_ram' => $totalRam . 'MB',
            'total_swap' => $totalSwap . 'MB',
            'free_swap' => $freeSwap . 'MB',
            'cpu_temp' => $cpuTemp / 1000 . '°C',
            'cpu_cores' => $totalCores,
            'cpu_usage' => $cpuUsage . '%',
            'ip_address' => $ipAddress,
            'mac_addr' => $macAddr,
            'os_release' => $osRelease,
            'disk_usage' => $diskUsage,
            'disk_space_total' => $totalDiskSpace . 'GB',
            'disk_space_free' => $freeDiskSpace . 'GB',
            'disk_space_used' => $usedDiskSpace . 'GB',
            'disk_space_used_percent' => $memoryUsage . '%',
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
