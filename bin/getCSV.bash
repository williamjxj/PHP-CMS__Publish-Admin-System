#! /bin/bash
# clear break # clear comp # clear col # set term off # set heading off # set echo off # set pagesize 0
# set linesize 9999 # set trimspool on # set tab off # set feedback off # set recsep off

ERR_LOG='/tmp/merrors.log'

# command-line params: 1 or NULL.
if [ $# -ne 0 ]; then
	param=$1
fi

txt1='employer.txt'
txt2='member.txt'
txt3='depmast.txt'
txt4='hr_bank.txt'

csv1='EMPLOYERS.csv'
csv2='MEMBERS.csv'
csv3='DEPENDENTS.csv'
csv4='BENEFITS.csv'


MYSQL='/usr/bin/mysql'
DB="pams"
USER="cdatcom_pams"
PASS='!@#$%^&*()ZCBM'

cd /home/backup/DBs/monthly/


########################################################
#1. employers:  START=$(date +%s); echo "It took $DIFF seconds to get EMPLOYERS data."
# touch EMPLOYERS.csv

function employers_1() {

START=$(date '+%F %T')
# If not exists, create it; if existing, don't touch.
if [ -f "$txt1" ]; then 
	/bin/mv -f $txt1 bak/  2>>$ERR_LOG
fi
if [ -f "$csv1" ]; then 
	/bin/mv -f $csv1 bak/  2>>$ERR_LOG
fi
touch $csv1 2>>$ERR_LOG

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
INSERT into monthly_records(file,start,end,total,result) values('$csv1', '$START', '', 0, 'U');
EOF

sqlplus -S -L GOGEE/eegog@216.18.10.113/cdat <<__EOF__
set colsep ,
set echo off
set feedback off
set linesize 1000
set sqlprompt ''
set pagesize 0
set trimspool on
set headsep off

spool /home/backup/DBs/monthly/employer.txt

select * from icba.employer_vw;
spool off
__EOF__

cat $txt1 | sed -e "s/[[:space:]]\+,/,/g"  -e "s/,\s\+/,/g"  -e "s/^\s\+//g" >$csv1 2>>$ERR_LOG

END=$(date '+%F %T')

# If canceled 3 times, there should at least 4 records on the table; only update latest.
$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
UPDATE monthly_records SET  end='$END', result='P', total=TIMESTAMPDIFF(SECOND, '$START', '$END') WHERE file='$csv1' AND result='U'
EOF
}

########################################################
#2. members

function members_2() {

START=$(date '+%F %T')
if [ -f "$txt2" ]; then
	/bin/mv -f $txt2 bak/  2>>$ERR_LOG
fi
if [ -f "$csv2" ]; then
	/bin/mv -f $csv2  bak/  2>>$ERR_LOG
fi
touch $csv2 2>>$ERR_LOG

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
INSERT into monthly_records(file,start,end,total,result) values('$csv2', '$START', '', 0, 'U');
EOF

sqlplus -S -L GOGEE/eegog@216.18.10.113/cdat <<__EOF__
set colsep ,
set echo off
set feedback off
set linesize 1000
set sqlprompt ''
set pagesize 0
set trimspool on
set headsep off

spool /home/backup/DBs/monthly/member.txt

select * from icba.member_vw;
spool off;
__EOF__

# not work?
cat $txt2 | sed -e "s/\s\+,/,/g" -e "s/,\s\+/,/g" -e "s/^\s\+//g" >$csv2 2>>$ERR_LOG

END=$(date '+%F %T')

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
UPDATE monthly_records SET  end='$END', result='P', total=TIMESTAMPDIFF(SECOND, '$START', '$END') WHERE file='$csv2' AND result='U'
EOF
}

########################################################
#3. dependents.

function dependents_3() {

START=$(date '+%F %T')
if [ ! -f "$txt3" ]; then
	/bin/mv -f $txt3 bak/  2>>$ERR_LOG
fi
if [ -f "$csv3" ]; then
	/bin/mv -f $csv3  bak/  2>>$ERR_LOG
fi
touch $csv3 2>>$ERR_LOG

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
INSERT into monthly_records(file,start,end,total,result) values('$csv3', '$START', '', 0, 'U');
EOF

sqlplus -S -L GOGEE/eegog@216.18.10.113/cdat <<__EOF__
set colsep ,
set echo off
set feedback off
set linesize 1000
set sqlprompt ''
set pagesize 0
set trimspool on
set headsep off

spool /home/backup/DBs/monthly/depmast.txt

select * from icba.depmast_vw;
spool off
__EOF__

cat $txt3 | sed -e "s/\s\+,/,/g"  -e "s/,\s\+/,/g" -e "s/^\s\+//g" >$csv3 2>>$ERR_LOG

END=$(date '+%F %T')

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
UPDATE monthly_records SET  end='$END', result='P', total=TIMESTAMPDIFF(SECOND, '$START', '$END') WHERE file='$csv3' AND result='U' 
EOF
}

########################################################
#4. benefits.

function benefits_0() {

START=$(date '+%F %T')
if [ -f  "$txt4" ]; then
	/bin/mv -f $txt4 bak/  2>>$ERR_LOG
fi
if [ -f  "$csv4" ]; then
	/bin/mv -f $csv4 bak/ 2>>$ERR_LOG
fi
touch $csv4 2>>$ERR_LOG

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
INSERT into monthly_records(file,start,end,total,result) values('$csv4', '$START', '', 0, 'U');
EOF

sqlplus -S -L GOGEE/eegog@216.18.10.113/cdat <<__EOF__
set colsep ,
set echo off
set feedback off
set linesize 1000
set sqlprompt ''
set pagesize 0
set trimspool on
set headsep off

spool /home/backup/DBs/monthly/hr_bank.txt

select * from icba.hr_bank_vw;

spool off
__EOF__

cat $txt4 | sed -e "s/\s\+,/,/g"  -e "s/,\s\+/,/g" -e "s/^\s\+//g" >$csv4 2>>$ERR_LOG

END=$(date '+%F %T')

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
UPDATE monthly_records SET  end='$END', result='P', total=TIMESTAMPDIFF(SECOND, '$START', '$END') WHERE file='$csv4' AND result='U'
EOF
}


###################
# Start From Here:
###################

if [ $# -eq 0 ]; then 
	b=''
	for b in `echo "SELECT file FROM monthly_records WHERE result='U'" | $MYSQL --skip-column-names -u "${USER}" -p"${PASS}" -h localhost -D ${DB}`
	do
		if [[ "$b" =~ 'BENEFITS' ]]; then
			benefits_0
		elif [[ "$b" =~ 'EMPLOYERS' ]]; then
			employers_1
		elif [[ "$b" =~ 'MEMBERS' ]]; then
			members_2
		elif [[ "$b" =~ 'DEPENDENTS' ]]; then
			dependents_3
		fi
		param=$b
	done
	if [ ! $b ]; then
			members_2
			dependents_3
			employers_1
			benefits_0
	fi
	param='All 4 files: members,dependents,employers,benefits'
else
    if [ X"$param" = X'members' ]; then
		members_2
    elif [ X"$param" = X'dependents' ]; then
		dependents_3
    elif [ X"$param" = X'employers' ]; then
		employers_1
    elif [ X"$param" =  X'benefits' ]; then
		benefits_0
    else
        echo $param $0 ': Error Here.'
    fi
fi

########################################################
# After upload whatever file(s), update monthly_notice table

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
    update monthly_notice set enable_process = 'Y' WHERE enable_process='N';
EOF

#2. send mail to notice user.
#echo "The monthly data upload processing [ $param ] is done at `date '+%F %T'`." | /bin/mail -s "Data monthly upload processing is done." william@gogeesoftware.com,gerald@cdat.com
echo "The monthly data upload processing [ $param ] is done at `date '+%F %T'`." | /bin/mail -s "Data monthly upload processing is done." william@gogeesoftware.com

