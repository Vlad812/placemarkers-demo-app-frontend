> Этот сервис: `app-frontend` является частью приложения [placemarkers-demo-workstation](https://github.com/Vlad812/placemarkers-demo-workstation).

# API Documentation: App Frontend (BFF)

**Стек технологий:**
- PHP 8.5
- Symfony 8
- RoadRunner
- Bootstrap 5
- jQuery

Backend-for-Frontend сервис. JSON API-эндпоинты проксируют запросы в микросервисы [`api-placemarkers-database`](https://github.com/Vlad812/placemarkers-demo-api-database.git), [`api-placemarkers-search`](https://github.com/Vlad812/placemarkers-demo-api-search.git) и [`api-placemarkers-collection`](https://github.com/Vlad812/placemarkers-demo-api-collection.git), а авторизация выполняется через [`auth-service`](https://github.com/Vlad812/placemarkers-demo-auth-service.git).

**Авторизация (общее):** сессия (cookie). `app-frontend` выступает BFF-слоем перед `auth-service`: после входа через `/login` access token и refresh token, выданные `auth-service`, сохраняются на сервере в сессии, а клиент передаёт только session cookie, без заголовка `Authorization: Bearer`. Хранилище сессий вынесено в Redis и обслуживается стандартным Symfony Session компонентом. При истечении access token BFF может использовать сохранённый refresh token для запроса новой пары токенов в `auth-service`, не перекладывая эту логику на браузер.

**Формат успешного ответа:** BFF возвращает успешный ответ внутреннего сервиса как есть, без изменений.

**Формат ошибок BFF:**

```json
{
  "message": "Текст ошибки"
}
```

При непредвиденной ошибке дополнительно возвращается `incident_id`.

---

## Создание новой метки (Create Placemarker)

Проксирует запрос в `api-placemarkers-database`.

**URL:** `/api/placemarkers`  
**Метод:** `POST`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`
* Session cookie

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `name` | `string` | **Да** | Название метки (не пустое, max 255). | `"Любимая кофейня"` |
| `lat` | `number` | **Да** | Широта (−90…90). | `55.755826` |
| `lon` | `number` | **Да** | Долгота (−180…180). | `37.617299` |
| `description` | `string` | Нет | Описание (max 2000). | `"Отличный кофе и круассаны"` |
| `type_id` | `string` | Нет | Идентификатор типа метки. | `"cafe"` |
| `tags` | `array` | Нет | Массив идентификаторов тегов. | `["coffee", "wifi"]` |

#### Пример запроса

```json
POST /api/placemarkers HTTP/1.1
Content-Type: application/json
Cookie: PHPSESSID=...

{
  "name": "Любимая кофейня",
  "lat": 55.755826,
  "lon": 37.617299,
  "description": "Отличный кофе и круассаны",
  "type_id": "cafe",
  "tags": ["coffee", "wifi"]
}
```

### Responses (Ответы)

#### 🟢 201 Created — Успешное создание

Тело ответа — от `api-placemarkers-database`.

```json
{
  "id": "123e4567-e89b-12d3-a456-426614174000",
  "name": "Любимая кофейня",
  "lat": "55.75582600",
  "lon": "37.61729900",
  "type_id": "cafe",
  "tags": ["coffee", "wifi"],
  "description": "Отличный кофе и круассаны",
  "status": true,
  "msg": "Метка сохранена"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось создать метку."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

Сообщение проксируется из downstream-сервиса.

```json
{
  "message": "Missing required parameter \"name\"."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис меток временно недоступен. Попробуйте позже."
}
```

---

## Обновление метки (Update Placemarker)

Проксирует запрос в `api-placemarkers-database`. Координаты (`lat`, `lon`) через этот эндпоинт изменить нельзя.

**URL:** `/api/placemarkers/{id}`  
**Метод:** `PUT`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`
* Session cookie

#### Параметры пути (Path Parameters)

| Параметр | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `uuid` | **Да** | Идентификатор метки. | `"123e4567-e89b-12d3-a456-426614174000"` |

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `name` | `string` | **Да** | Новое название (не пустое, max 255). | `"Обновлённая кофейня"` |
| `description` | `string` | Нет | Новое описание (max 2000). | `"Теперь с десертами"` |
| `type_id` | `string` | Нет | Новый тип. Если не передан — не меняется. | `"restaurant"` |
| `tags` | `array` | Нет | Новый список тегов. Если не передан — не меняется. | `["coffee", "dessert"]` |

#### Пример запроса

```json
PUT /api/placemarkers/123e4567-e89b-12d3-a456-426614174000 HTTP/1.1
Content-Type: application/json
Cookie: PHPSESSID=...

{
  "name": "Обновлённая кофейня",
  "description": "Теперь с десертами",
  "type_id": "restaurant",
  "tags": ["coffee", "dessert"]
}
```

### Responses (Ответы)

#### 🟢 200 OK — Успешное обновление

```json
{
  "id": "123e4567-e89b-12d3-a456-426614174000",
  "name": "Обновлённая кофейня",
  "lat": "55.75582600",
  "lon": "37.61729900",
  "type_id": "restaurant",
  "tags": ["coffee", "dessert"],
  "description": "Теперь с десертами",
  "status": true,
  "msg": "Метка обновлена"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось обновить метку."
}
```

#### 🔴 404 Not Found — Метка не найдена

```json
{
  "message": "Placemarker with id \"123e4567-e89b-12d3-a456-426614174000\" was not found."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Missing required parameter \"name\"."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис меток временно недоступен. Попробуйте позже."
}
```

---

## Удаление метки (Delete Placemarker)

Проксирует запрос в `api-placemarkers-database`.

**URL:** `/api/placemarkers/{id}`  
**Метод:** `DELETE`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* Session cookie

#### Параметры пути (Path Parameters)

| Параметр | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `uuid` | **Да** | Идентификатор метки. | `"123e4567-e89b-12d3-a456-426614174000"` |

#### Пример запроса

```http
DELETE /api/placemarkers/123e4567-e89b-12d3-a456-426614174000 HTTP/1.1
Cookie: PHPSESSID=...
```

### Responses (Ответы)

#### 🟢 200 OK — Успешное удаление

```json
{
  "status": true,
  "msg": "Метка удалена"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось удалить метку."
}
```

#### 🔴 404 Not Found — Метка не найдена

```json
{
  "message": "Placemarker with id \"123e4567-e89b-12d3-a456-426614174000\" was not found."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Value \"invalid-id\" is not a valid UUID."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис меток временно недоступен. Попробуйте позже."
}
```

---

## Создание тега (Create Tag)

Проксирует запрос в `api-placemarkers-database`.

**URL:** `/api/tags`  
**Метод:** `POST`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`
* Session cookie

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `name` | `string` | **Да** | Название тега (не пустое, max 255). | `"coffee"` |
| `description` | `string` | Нет | Описание тега. | `"Места с хорошим кофе"` |

#### Пример запроса

```json
POST /api/tags HTTP/1.1
Content-Type: application/json
Cookie: PHPSESSID=...

{
  "name": "coffee",
  "description": "Места с хорошим кофе"
}
```

### Responses (Ответы)

#### 🟢 201 Created — Успешное создание

```json
{
  "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "name": "coffee",
  "description": "Места с хорошим кофе",
  "status": true,
  "msg": "Tag created successfully"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось создать тег."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Missing required parameter \"name\"."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис меток временно недоступен. Попробуйте позже."
}
```

---

## Поиск меток (Search Placemarkers)

Проксирует запрос в `api-placemarkers-search` (`GET /search`).

**URL:** `/api/search`  
**Метод:** `GET`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* Session cookie

#### Query Parameters

| Параметр | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `lat` | `number` | **Да** | Широта (−90…90). | `55.755826` |
| `lon` | `number` | **Да** | Долгота (−180…180). | `37.617299` |
| `radius` | `number` | **Да** | Радиус в метрах (> 0). | `1500` |
| `tags` | `string` / `array<string>` | Нет | Фильтр по тегам. | `["coffee", "wifi"]` |
| `types` | `string` / `array<string>` | Нет | Фильтр по типам. | `["cafe"]` |

#### Пример запроса

```http
GET /api/search?lat=55.755826&lon=37.617299&radius=1500&tags=coffee&types=cafe HTTP/1.1
Cookie: PHPSESSID=...
```

### Responses (Ответы)

#### 🟢 200 OK — Успешный поиск

```json
[
  {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "name": "Любимая кофейня",
    "lat": 55.755826,
    "lon": 37.617299,
    "type_id": "cafe",
    "tags": ["coffee", "wifi"],
    "description": "Отличный кофе"
  }
]
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось выполнить поиск."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Missing required parameter: lat."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис поиска временно недоступен. Попробуйте позже."
}
```

---

## Получение метки по ID (Get Placemarker)

Проксирует запрос в `api-placemarkers-search` (`GET /search/placemarkers/{id}`).

**URL:** `/api/search/placemarkers/{id}`  
**Метод:** `GET`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* Session cookie

#### Параметры пути (Path Parameters)

| Параметр | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `uuid` | **Да** | Идентификатор метки. | `"123e4567-e89b-12d3-a456-426614174000"` |

#### Пример запроса

```http
GET /api/search/placemarkers/123e4567-e89b-12d3-a456-426614174000 HTTP/1.1
Cookie: PHPSESSID=...
```

### Responses (Ответы)

#### 🟢 200 OK — Метка найдена

```json
{
  "id": "123e4567-e89b-12d3-a456-426614174000",
  "name": "Любимая кофейня",
  "lat": 55.755826,
  "lon": 37.617299,
  "type_id": "cafe",
  "tags": ["coffee", "wifi"],
  "description": "Отличный кофе",
  "created_at": "2026-07-13 10:30:00"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось загрузить метку."
}
```

#### 🔴 404 Not Found — Метка не найдена

```json
{
  "message": "Placemarker with id \"123e4567-e89b-12d3-a456-426614174000\" was not found."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Parameter id must be a valid UUID."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис поиска временно недоступен. Попробуйте позже."
}
```

---

## Список коллекций (Get User Collections)

Проксирует запрос в `api-placemarkers-collection` (`GET /collections`).

**URL:** `/api/collections`  
**Метод:** `GET`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* Session cookie

#### Пример запроса

```http
GET /api/collections HTTP/1.1
Cookie: PHPSESSID=...
```

### Responses (Ответы)

#### 🟢 200 OK — Успешный ответ

```json
{
  "status": "success",
  "data": [
    {
      "id": "col-123e4567-e89b-12d3-a456-426614174000",
      "name": "Подборка",
      "createdAt": "2026-07-13T10:30:00+00:00",
      "searchCriteria": {
        "latitude": 59.94,
        "longitude": 30.31,
        "radiusMeters": 1000
      },
      "placemarkersCount": 1,
      "placemarkers": [
        {
          "originalId": "123e4567-e89b-12d3-a456-426614174000",
          "title": "Любимая кофейня",
          "latitude": 59.94,
          "longitude": 30.31,
          "typeId": "cafe",
          "description": "Отличный кофе",
          "tags": ["coffee"]
        }
      ]
    }
  ]
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось загрузить коллекции."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис коллекций временно недоступен. Попробуйте позже."
}
```

---

## Сохранение коллекции (Save Collection)

Проксирует запрос в `api-placemarkers-collection` (`POST /collections`).

**URL:** `/api/collections`  
**Метод:** `POST`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`
* Session cookie

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `name` | `string` | **Да** | Название коллекции (не пустое, max 255). | `"Подборка"` |
| `search_criteria` | `object` | **Да** | Критерии поиска, по которым была собрана коллекция. | |
| `search_criteria.latitude` | `number` | **Да** | Широта (−90…90). | `59.94` |
| `search_criteria.longitude` | `number` | **Да** | Долгота (−180…180). | `30.31` |
| `search_criteria.radius` | `number` | **Да** | Радиус в метрах (> 0). | `1000` |
| `search_criteria.tags` | `array<string>` | Нет | Теги из критериев поиска. | `["coffee"]` |
| `search_criteria.types` | `array<string>` | Нет | Типы из критериев поиска. | `["cafe"]` |
| `placemarkers` | `array` | **Да** | Список меток (не пустой). | |
| `placemarkers[].originalId` | `string` | **Да** | Идентификатор исходной метки. | `"123e4567-..."` |
| `placemarkers[].title` | `string` | **Да** | Название метки. | `"Любимая кофейня"` |
| `placemarkers[].latitude` | `number` | **Да** | Широта. | `59.94` |
| `placemarkers[].longitude` | `number` | **Да** | Долгота. | `30.31` |
| `placemarkers[].description` | `string\|null` | Нет | Описание. | `"Отличный кофе"` |
| `placemarkers[].typeId` | `string` | Нет | Тип метки. | `"cafe"` |
| `placemarkers[].tags` | `array<string>` | Нет | Теги метки. | `["coffee"]` |

#### Пример запроса

```json
POST /api/collections HTTP/1.1
Content-Type: application/json
Cookie: PHPSESSID=...

{
  "name": "Подборка",
  "search_criteria": {
    "latitude": 59.94,
    "longitude": 30.31,
    "radius": 1000,
    "tags": ["coffee"],
    "types": ["cafe"]
  },
  "placemarkers": [
    {
      "originalId": "123e4567-e89b-12d3-a456-426614174000",
      "title": "Любимая кофейня",
      "latitude": 59.94,
      "longitude": 30.31,
      "description": "Отличный кофе",
      "typeId": "cafe",
      "tags": ["coffee"]
    }
  ]
}
```

### Responses (Ответы)

#### 🟢 201 Created — Успешное сохранение

```json
{
  "status": "success",
  "collection_id": "col-123e4567-e89b-12d3-a456-426614174000"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось сохранить коллекцию."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Missing collection name."
}
```

```json
{
  "message": "Placemarkers list must not be empty."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис коллекций временно недоступен. Попробуйте позже."
}
```

---

## Удаление коллекции (Delete Collection)

Проксирует запрос в `api-placemarkers-collection` (`DELETE /collections/{id}`).

**URL:** `/api/collections/{id}`  
**Метод:** `DELETE`  
**Авторизация:** Требуется (сессия)

### Request (Запрос)

#### Заголовки (Headers)
* Session cookie

#### Параметры пути (Path Parameters)

| Параметр | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `id` | `string` | **Да** | Идентификатор коллекции. | `"col-123e4567-..."` |

#### Пример запроса

```http
DELETE /api/collections/col-123e4567-e89b-12d3-a456-426614174000 HTTP/1.1
Cookie: PHPSESSID=...
```

### Responses (Ответы)

#### 🟢 200 OK — Успешное удаление

```json
{
  "status": "success"
}
```

#### 🔴 401 Unauthorized — Ошибка авторизации

```json
{
  "message": "Не удалось удалить коллекцию."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Missing id."
}
```

#### 🔴 503 Service Unavailable — Сервис недоступен

```json
{
  "message": "Сервис коллекций временно недоступен. Попробуйте позже."
}
```

---

## Проверка состояния сервиса (Health Check)

Проверяет доступность сервиса. Используется для мониторинга и оркестрации (Docker, Kubernetes).

**URL:** `/health`  
**Метод:** `GET`  
**Авторизация:** Не требуется

### Request (Запрос)

#### Пример запроса

```http
GET /health HTTP/1.1
```

### Responses (Ответы)

#### 🟢 200 OK — Сервис доступен

```json
{
  "status": "ok"
}
```
