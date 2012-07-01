#!/bin/bash

DB="cibp_xml"
DB_LIVE="cibp_test"
#DB_LIVE="cibp"
USER="cdatcom_cibp"
PASS='!#%&(24680zxcvbnm'

MYSQL='/usr/bin/mysql'
SRC='/home/backup/DBs/monthly'

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF
  delete from benefits;
  delete from dependents;
  delete from employers;
  delete from new_users;
EOF

cd ${SRC}
for file in `ls  *.csv`
do
 if [[ "$file" =~ "BENEFITS"  ]]
 then
   TABLE='benefits'
 elif [[ "$file" =~ "DEPENDENTS" ]]
 then
   TABLE='dependents'
 elif [[ "$file" =~ "EMPLOYERS" ]]
 then
   TABLE='employers'
 elif [[ "$file" =~ "MEMBERS" ]]
 then
   TABLE='new_users'
 else
   echo $file
   continue
 fi

echo "processing file [" $file "] to table [" $TABLE "] ...";

$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB} <<EOF

load data infile '${SRC}/${file}'
 into table ${TABLE}
 fields terminated by ','
 enclosed by '"'
 escaped by '\\\'
 lines terminated by '\n';
 \q
EOF
done

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

#################################################################
# mysqladmin create ${DB_TEMP} -u 'root' --password='zcurQCdg'
# Not Work! rename database db_name to new_db;
# echo "rename DATABASE ${DB_LIVE} to ${DB_TEMP} " | $MYSQL -u "root" -p"zcurQCdg" -h localhost;
# create
# mysqladmin create ${DB_LIVE} -u ${USER} --password=${PASS}
# clone 
# mysqldump -u $USER --password=${PASS} ${DB} | mysql -u $USER --password=${PASS} ${DB_LIVE}

#  select count(*) as 'table [benefits]:'  from benefits;
#  select count(*) as 'table[dependents]:'  from dependents;
#  select count(*) as 'table[employers]:'  from employers;
#  select count(*) as 'table[users]:'  from users;
#################################################################
  
$MYSQL -u "${USER}" -p"${PASS}" -h localhost -D ${DB_LIVE} <<EOF
  delete from benefits;
  delete from employers;
  delete from dependents;
  delete from users;

  insert into benefits select * from ${DB}.benefits;
  insert into dependents select * from ${DB}.dependents;
  insert into employers select * from ${DB}.employers;
  insert into users select * from ${DB}.users;
EOF

# After processing, reset monthly_notice table
PASS1='!@#$%^&*()ZCBM'
$MYSQL -u "cdatcom_pams" -p"${PASS1}"  -h localhost -D 'pams' <<EOF
	update monthly_notice set enable_process = 'N';
EOF

