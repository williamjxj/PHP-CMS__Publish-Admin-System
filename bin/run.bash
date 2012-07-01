#!/bin/bash
# use  2>>/tmp/merrors.log instead of 2>&1

if [ $# -eq 0 ]; then
 echo "What step do you want to start from?" >>/tmp/merrors.log
 exit 10;
fi
# setup log file.
if [ ! -f /tmp/merrors.log ]; then
	touch /tmp/merrors.log
	chmod 666 /tmp/merrors.log
fi

if [ $# -eq 4 ]
then
	nohup /home/backup/DBs/monthly/getCSV.bash  >/dev/null 2>/tmp/merrors.log &

else
    if [ $# -eq 1 ]; then
		curr=`ps -ef | grep getCSV | grep -v grep |grep -v vi | grep $1 | wc -l`
		if [ $curr -ne 0 ]; then
			echo $(date) $1 " Still running. Quit." >>/tmp/merrors.log
			exit 1
		fi
		nohup /home/backup/DBs/monthly/getCSV.bash  $1 >/dev/null 2>>/tmp/merrors.log  &

    elif [ $# -eq 2 ]; then
		curr=`ps -ef | grep getCSV | grep -v grep |grep -v vi | grep $1 | wc -l`
		if [ $curr -ne 0 ]; then
			echo $(date) $1 " Still running. Quit." >>/tmp/merrors.log
		else
			nohup /home/backup/DBs/monthly/getCSV.bash  $1 >/dev/null 2>>/tmp/merrors.log &
		fi

		curr=`ps -ef | grep getCSV | grep -v grep |grep -v vi | grep $2 | wc -l`
		if [ $curr -ne 0 ]; then
			echo $(date) $2 " Still running. Quit." >>/tmp/merrors.log
		else
			nohup /home/backup/DBs/monthly/getCSV.bash  $2 >/dev/null 2>>/tmp/merrors.log &
		fi

    elif [ $# -eq 3 ]; then
		curr=`ps -ef | grep getCSV | grep -v grep |grep -v vi | grep $1 | wc -l`
		if [ $curr -ne 0 ]; then
			echo $(date) $1 " Still running. Quit." >>/tmp/merrors.log
		else
			nohup /home/backup/DBs/monthly/getCSV.bash  $1 >/dev/null 2>>/tmp/merrors.log &
		fi

		curr=`ps -ef | grep getCSV | grep -v grep |grep -v vi | grep $2 | wc -l`
		if [ $curr -ne 0 ]; then
			echo $(date) $2 " Still running. Quit." >>/tmp/merrors.log
		else
			nohup /home/backup/DBs/monthly/getCSV.bash  $2 >/dev/null 2>>/tmp/merrors.log &
		fi

		curr=`ps -ef | grep getCSV | grep -v grep |grep -v vi | grep $3 | wc -l`
		if [ $curr -ne 0 ]; then
			echo $(date) $3 " Still running. Quit." >>/tmp/merrors.log
		else
			nohup /home/backup/DBs/monthly/getCSV.bash  $3 >/dev/null 2>>/tmp/merrors.log &
		fi
    else
        echo $# $1 $2 $3 ': Error Here in ' $0 '.'
    fi
fi
