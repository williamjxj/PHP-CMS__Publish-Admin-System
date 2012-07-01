#!/bin/bash

sqlplus  -S -L GOGEE/eegog@216.18.10.113/cdat <<__EOF__
select count(*) as MEMBERS from icba.member_vw;
select count(*) as EMPLOYERS from icba.employer_vw;
select count(*) as DEPENDENTS from icba.depmast_vw;
select count(*) as BENEFITS from icba.hr_bank_vw;
__EOF__

