<?php
// +----------------------------------------------------------------------
// |  
// | test4.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-30 21:18
// +----------------------------------------------------------------------

        //创建一个子进程
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new Exception('fork子进程失败');
        } elseif ($pid > 0) {
            //父进程退出,子进程变成孤儿进程被1号进程收养，进程脱离终端
            exit(0);
        }

        //创建一个新的会话，脱离终端控制，更改子进程为组长进程
        $sid = posix_setsid();
        if ($sid == -1) {
            throw new Exception('setsid fail');
        }

        //修改当前进程的工作目录，由于子进程会继承父进程的工作目录，修改工作目录以释放对父进程工作目录的占用。
        chdir('/');

        /**
         * 通过上一步，我们创建了一个新的会话组长，进程组长，且脱离了终端，但是会话组长可以申请重新打开一个终端，为了避免
         * 这种情况，我们再次创建一个子进程，并退出当前进程，这样运行的进程就不再是会话组长。
         */
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('fork子进程失败');
        } elseif ($pid > 0) {
            //再一次退出父进程，子进程成为最终的守护进程
            exit(0);
        }
        //由于守护进程用不到标准输入输出，关闭标准输入，输出，错误输出描述符
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        $pid = posix_getpid();

        $filename = "../storage/logs/worker.{$pid}.log";

        if(file_exists($filename)){
            unlink($filename);
        }

        for($i = 0; $i < 36; $i++){
            echo $content = "PID:{$pid},".date('Y-m-d H:i:s').PHP_EOL;

            file_put_contents($filename, $content, FILE_APPEND);

            sleep(1);
        }

        echo "End...",PHP_EOL;

        sleep(60);