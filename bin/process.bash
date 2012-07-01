#!/bin/bash

MYSQL='/usr/bin/mysql'
SRC='/home/backup/DBs/monthly'

DB="cibp_xml"
DB_LIVE="cibp_test"
#DB_LIVE="cibp"
USER="cdatcom_cibp"
PASS='!#%&(24680zxcvbnm'

DB1='pams'
USER1='cdatcom_pams'
PASS1='!@#$%^&*()ZCBM'

for b in `echo "SELECT distinct file FROM monthly_records WHERE result='P'" | $MYSQL --skip-column-names -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}`
do
	if [[ "$b" =~ 'BENEFITS' ]]; then
		TABLE='benefits'
		echo "DELETE FROM benefits" | $MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} 
	elif [[ "$b" =~ 'EMPLOYERS' ]]; then
		TABLE='employers'
		echo "DELETE FROM employers" | $MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} 
	elif [[ "$b" =~ 'MEMBERS' ]]; then
		TABLE='new_users'
		echo "DELETE FROM new_users" | $MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} 
		FLAG=true
	elif [[ "$b" =~ 'DEPENDENTS' ]]; then
		TABLE='dependents'
		echo "DELETE FROM dependents" | $MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} 
	fi  

	file=${SRC}/$b

  $MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
load data local infile '$file'
 into table ${TABLE}
 fields terminated by ','
 enclosed by '"'
 escaped by '\\\'
 lines terminated by '\n';
 \q
EOF

done

if [ $FLAG ]; then
	$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
insert into  users(gwl,surname,given,birthdate,sex,employer,dcode,address1,address2,city,prov,postalcode,phone,email,beneficiary,relationship,occupationcode,enrollcarddate)
select * from new_users
ON DUPLICATE KEY UPDATE
users.surname=new_users.surname,
users.given=new_users.given,
users.birthdate=new_users.birthdate,
users.sex=new_users.sex,
users.employer=new_users.employer,
users.dcode=new_users.dcode,
users.address1=new_users.address1,
users.address2=new_users.address2,
users.city=new_users.city,
users.prov=new_users.prov,
users.postalcode=new_users.postalcode,
users.phone=new_users.phone,
users.email=new_users.email,
users.beneficiary=new_users.beneficiary,
users.relationship=new_users.relationship,
users.occupationcode=new_users.occupationcode,
users.enrollcarddate=new_users.enrollcarddate;
EOF
	FLAG=''
fi

# 1. 
file='BENEFITS.csv'
tt=`echo "SELECT distinct file FROM monthly_records WHERE result='P' AND file='$file'" | $MYSQL --skip-column-names -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}`
if [ $tt ]; then
$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB_LIVE} <<EOF
  delete from benefits;
  insert into benefits select * from ${DB}.benefits;
EOF
echo "UPDATE monthly_records SET result='Y' WHERE result='P' AND file='$file' " | $MYSQL -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}
fi

# 2.
file='DEPENDENTS.csv'
tt=`echo "SELECT distinct file FROM monthly_records WHERE result='P' AND file='$file'" | $MYSQL --skip-column-names -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}`
if [ $tt ]; then
$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB_LIVE} <<EOF
  delete from dependents;
  insert into dependents select * from ${DB}.dependents;
EOF
echo "UPDATE monthly_records SET result='Y' WHERE result='P' AND file='$file' " | $MYSQL -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}
fi

# 3.
file='EMPLOYERS.csv'
tt=`echo "SELECT distinct file FROM monthly_records WHERE result='P' AND file='$file'" | $MYSQL --skip-column-names -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}`
if [ $tt ]; then
$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB_LIVE} <<EOF
  delete from employers;
  insert into employers select * from ${DB}.employers;
EOF
echo "UPDATE monthly_records SET result='Y' WHERE result='P' AND file='$file' " | $MYSQL -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}
fi

# 4. 
file='MEMBERS.csv'
tt=`echo "SELECT distinct file FROM monthly_records WHERE result='P' AND file='$file'" | $MYSQL --skip-column-names -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}`
if [ $tt ]; then
$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB_LIVE} <<EOF
  delete from users; 
  insert into users select * from ${DB}.users;
EOF
echo "UPDATE monthly_records SET result='Y' WHERE result='P' AND file='$file' " | $MYSQL -u "${USER1}" -p"${PASS1}" -h localhost -D ${DB1}
fi



# After processing, reset monthly_notice table
$MYSQL -u "${USER1}" -p"${PASS1}"  -h localhost -D "${DB1}" <<EOF
	update monthly_notice set enable_process = 'N';
EOF

