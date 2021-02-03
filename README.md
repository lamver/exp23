# Referral Tree

## Консольное приложение на базе Yii2

### Команды:

#### referral/buildtree
Построить дерево рефералов на основе поля partner_id таблицы Users:
(-pid - обязательный параметр) Пример:

 `php yii referral/build-tree -pid=82824897`


#### referral/totalvolume
Посчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:
(-pid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:

`php yii referral/total-volume -pid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00`


#### referral/totalprofit
Посчитать прибыльность (сумма profit) за определенный период времени:
(-pid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:

`php yii referral/total-profit -pid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00`


#### referral/getcountreferral
Посчитать количество прямых рефералов и количество всех рефералов клиента:
(-pid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:

`php yii referral/count-referral -pid=82824897 -refdir=1`


#### referral/countlevel
Посчитать количество уровней реферальной сетки:
(-pid - обязательный параметр) Пример:

`php yii referral/count-level -pid=82824897`

