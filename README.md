# Referral Tree

## Консольное приложение на базе Yii2

## Как установить

```
git clone https://github.com/lamver/exp23.git
```

```
composer update
```

#### Прописать данные подключения к базе данных

```
config/db.php
```

## Как пользоваться 
### Команды:

#### referral/build-tree
Построить дерево рефералов на основе поля partner_id таблицы Users:
(-uid - обязательный параметр) Пример:

```
php yii referral/build-tree -uid=82824897
```


#### referral/total-volume
Посчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:
(-uid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:

```
php yii referral/total-volume -uid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00
```


#### referral/total-profit
Посчитать прибыльность (сумма profit) за определенный период времени:
(-uid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:

```
php yii referral/total-profit -uid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00
```


#### referral/count-referral
Посчитать количество прямых рефералов и количество всех рефералов клиента:
(-uid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:

```
php yii referral/count-referral -uid=82824897 -refdir=1
```


#### referral/count-level
Посчитать количество уровней реферальной сетки:
(-uid - обязательный параметр) Пример:

```
php yii referral/count-level -uid=82824897
```

