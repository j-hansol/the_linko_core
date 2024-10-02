# 더 링코 코어 프로젝트 (The Linko Core)

## 프로젝트 참여 구성원의 역할
현재의 프로젝트는 비공개 프로젝트로 아래 역할을 가진 구성원만 접근 가능합니다.
* Owner (프로젝트 소유자)
* Maintainer (유지 관리자)
* Developer (개발자)
* Reporter (보고자)

위 역할 중 Owner 와 Maintainer 역할을 가진 구성원이 마스터(Master) 브렌치에 Merge / Push 작업이 가능합니다.
Developer 는 별도의 브렌치를 생성하여 작업하고 작업 결과를 Push 한 후 Owner, Maintainer 역할의 구성원에게 병합(Merge) 요청을 해주세요.

## 개발환경 구성
본 프로젝트는 PHP 8.2, Laravel 10 프레임워크를 기반으로 개발되고 있습니다. PHP 8.2과 Laravel 10을 지원하는 환경이라면 문제가 없습니다. 만일 환경이 구성되어 있지 않고, 구성에 시간을 아끼고자 한다면 아래외 Docker 환경을 구성해보세요.

### Docker 개발환경 구성
1. [Docker Desktop](https://www.docker.com/products/docker-desktop/)을 다운로드하여 설치합니다.
2. 설치 후 Docker Engine 을 실행합니다.
3. 프로젝트를 진행할 폴더를 생성합니다. (Docker 개발환경 홈으로 부르겠습니다.)
4. 폴더 아래에 ```webroot``` 폴더를 생성하고 그 아래에 ```sites```, ```site``` 폴더를 생성합니다.
5. ```docker-compose.yml``` 파일을 작성합니다.
6. ```docker-compose``` 명령을 이용하여 개발환경을 실행합니다.

### ```docker-compose.yml```
아래의 YML 파일은 현재 본 프로젝트에서 사용하고 있는 파일입니다. 아래 내용을 참고하여 Docker 개발환경 홈 폴더에 저장합니다.
```yaml
version: "3.8"
volumes:
  sql_data:

services:
  db:
    image: pig482/mysql:kr
    container_name: db
    environment:
      MYSQL_ROOT_PASSWORD: mysql
    ports:
      - 3306:3306
    volumes:
      - sql_data:/var/lib/mysql

  web:
    image: pig482/devenv:p82
    container_name: web
    ports:
      - 80:80
      - 443:443
      - 5173:5173
      - 8080:8080
    volumes:
      - ./webroot:/DevHome
```

위 파일이 생성되었다면 아래의 명령으로 개발환경을 실행합니다.
```bash
docker-compose up -d
```

그리고 아래 명령으로 개발환경을 종료할 수 있습니다.
```bash
docker-compose down
```

## 프로젝트 설정
개발 프로젝트를 진행하기 위해서는 아래의 작업을 먼저 진행해주세요. 그리고 설명은 Docker 개발환경을 기준으로 하겠습니다.

1. DBMS(MySQL)에 프로젝트를 위한 데이터베이스를 생성합니다.
2. 사내 [Git 저장소](https://git.sohocode.kr/service/the-linko/core)로부터 소스를 ```Docker 홈/webroot/sites``` 폴더에 복사합니다.
3. ```.env-example``` 파일 내용을 복사하여 DB 연결부분을 수정하여 ```.env``` 라는 이름으로 저장합니다.
4. 개발환경의 Web 컨테이너에서 패키지 설치, 어플리케이션 키 생성, 마이그레이션(Migration), 씨딩(Seeding), NodeJs 패키지 설치 등을 진행합니다

### .env 파일
```ini
# ......... 생량 ..........
# 변경 필수
APP_URL="https://public.the-linko-core.wd"

# ......... 중간 생력 ...........

DB_CONNECTION=mysql
# Docker 로컬 개발환경에서는 host.docker.internal로 설정
DB_HOST="데이터베이스 서버 주소"
DB_PORT=3306
DB_DATABASE="에티이터베이스명"
DB_USERNAME="계정명"
DB_PASSWORD="계정 비밀번호"

# ........ 이후 내용 생략 ..........
```

### 데이터베이스 생성
데이터베이스는 utf8mb4 문자셋으로 설정하고, 정열 규칙(Collation) utf8mb4_unicode_ci 로 설정합니다.

### 초기 설정을 위한 명령어 셋
본 내용은 ```Docker 개발환경``` 을 위주로 작성하겠습니다.
먼저 아래와 같이 Web 컨테이너에 진입합니다.
```bash
docker-compose exec web bash
```

그리고 아래와 같이 프로젝트 폴더로 이동하여 초기 설정 작업을 진행합니다.
```bash
cd core
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
```
