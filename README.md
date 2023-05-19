# Дополнительные задания для Simtech

### Значения по умолчанию:
- Логин и пароль mysql юзера - `dev` / `root`
- Название базы данных - `cscartmods`
- Логин и пароль администратора cscart - `dev@example.com` / `root`
- Адрес phpMyAdmin - `http://admin.devel/`
- Адрес cscart - `http://cscartmods.devel/`
- Путь до папки с cscart - `~/apache_htdocs/public_html/cscartmods.devel`
- Бэкап базы данных лежит в: `var/backups/cscartmods.sql.zip`

## Установка
<details>
  <summary><h3>Установка зависимостей и настройка</h3></summary>
  
### Требуемые зависимости:
1. Apache2
2. MariaDB
3. PHP 7.4
4. CS-Cart v4.16.2_ru

### Установка необходимых зависимостей:
```sh
sudo apt install mariadb-server apache2
```
```sh
sudo add-apt-repository ppa:ondrej/php
```
```sh
sudo apt update
```
```sh
sudo apt install php7.4 php7.4-curl php7.4-xdebug \
php7.4-mysql php7.4-soap php7.4-zip \
php7.4-gd php7.4-xml php7.4-iconv \
php7.4-mbstring git
```

### То сначала нужно вызывать:

1. `sudo apt install libgd3`
2. А затем установить PHP

### Настройка git
```sh
mkdir -p ~/apache_htdocs/public_html/cscartmods.devel ; \
cd ~/apache_htdocs/public_html/cscartmods.devel
```
- Склонировать текущий репозиторий в папку
- Затем:

```sh
mv cscart-mods-tasks/* . ; \
mv cscart-mods-tasks/.* . ; \
rmdir cscart-exercise-1
```

### Настройка mysql
```sh
sudo mysql
```

```mysql
CREATE USER 'dev'@'localhost' IDENTIFIED BY 'root';
```

```mysql
GRANT ALL PRIVILEGES ON *.* TO 'dev'@'localhost';
```

```mysql
FLUSH PRIVILEGES;
```

### Импорт базы данных 
```sh
mysql -u dev -p
```

```mysql
CREATE DATABASE cscartmods;
```

```sh
unzip -p ~/apache_htdocs/public_html/cscartmods.devel/var/backups/cscartmods.sql.zip \
| mysql -u dev -p cscartmods
```

### Настройка apache2
```sh
sudo cp readme/hosts /etc/ ; \
sudo cp readme/apache2.conf /etc/apache2 ; \
sudo cp readme/cscartmods.devel.conf /etc/apache2/sites-available ; \
sudo cp readme/admin.devel.conf /etc/apache2/sites-available ; \
unzip readme/admin.devel.zip -d ~/apache_htdocs/public_html/ ; \
sudo sed -i "s/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=$USER/g" /etc/apache2/envvars ; \
sudo sed -i "s/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=$USER/g" /etc/apache2/envvars ; \
sudo a2ensite cscartmods.devel.conf admin.devel.conf ; \
sudo a2enmod rewrite ; sudo systemctl restart apache2
```
- Попробовать открыть в браузере http://cscartmods.devel и http://admin.devel

### Если не работает то:
```sh
sudo systemctl restart apache2 ; \
sudo chmod 775 -R ~/apache_htdocs ; \
sudo chown $USER -R ~/apache_htdocs
```
- После этого поидеи должно все заработать

</details>

## Инструкции

<details>
    <summary>
        <h3>Инструкция по модулю "Двухфакторная аутентификация"</h3>
     </summary>
    
### Настройка SMTP сервера для отправки писем
1. Перейти в настройки -> Электронная почта
2. Способ отправки почты -> `Через SMTP сервер`
3. SMTP сервер -> `smtp.yandex.ru:465`
4. Имя пользователя для SMTP ->  `datezzz@yandex.com`
5. Пароль для SMTP сервера -> `siclvmmvphunsqbd`
6. Шифрованное соединение -> `SSL`
7. Использовать SMTP аутентификацию -> `Да`
8. Сохранить
    
### Настройка почты с которой будет идти отправка
1. Перейти в настройки -> Компания
2. Email отдела поддержки -> `datezzz@yandex.com`
3. Сохранить
    
### Пометки
- Для администратора двухфакторная аутентификация отключена
- В модуле доступны настройки для кастомизации модуля
- В режиме разработки код не генерируется случайно.
- Чтобы код генерировался случайно, то нужно константе DEVELOPMENT установить значение false 
    
</details>

## Тест-кейс

<details>
    <summary><h3>Тест проверки возраста</h3></summary>
    
### Предусловия:
1. Есть тестовый магазин с установленным модулем
2. При заходе на сайт появляется диалог с подтверждением возраста
    
### Тесты:
1. Тест витрины
    
### Тест витрины:
1. Зайти на любую страницу витрины
2. Проверить, что появился всплывающий диалог с надписью `Подтвердите свой возраст`
3. Ввести возраст превышающий возраст указанный как - `Минимальный возраст для доступа`
4. Нажать на кнопку - `ОТПРАВИТЬ`
5. Подтвердить, что диалогового окна больше нет
    
### Ожидаемый результат:
- Всплывающий диалог доступен на любой странице витрины
- Ввод возраста работает без ошибок
- После отправки формы с возрастом превышающий минимальный возраст, диалоговое окно должно исчезнуть
  
</details>

<details>
    <summary><h3>Тест двухвакторной аутентификации</h3></summary>
    
### Предусловия:
1. Есть тестовый магазин с установленным модулем
2. Есть аккаунт покупателя
    
### Тесты:
1. Тест витрины
    
### Тест витрины:
1. Зайти на любую страницу витрины
2. Нажать на кнопку `Мой профиль`
3. Нажать на кнопку `ВОЙТИ`
4. Ввести данные покупателя
5. Нажать на кнопку `ВОЙТИ`
6. Убедиться, что мы на странице `Двухфакторной аутентификации`
7. Нажать на кнопку `ОТПРАВИТЬ КОД`
8. Зайти на свою почту и убедиться, что код пришел
9. Ввести код в поле `Код`
10. Нажать на кнопку `ПОДТВЕРДИТЬ КОД`
11. Нажать на кнопку `Мой профиль`
12. Убедиться, что вошли в аккаунт
    
### Ожидаемый результат:
- После попытки входа перебрасывает на страницу двухфакторной авторизации
- Код успешно отправляется и подтверждается
- После подтверждения кода, перенаправляемся на предыдущую страницу
- Вход в аккаунт осуществился успешно
  
</details>
