# API Structure

This document maps the API interfaces and external microservice integration routes.

---

## 1. Storefront Public APIs

### Check Serviceable Pincode
*   **Endpoint**: `/api/check-pincode`
*   **Method**: `GET`
*   **Parameters**:
    *   `pincode` (string, Required)
*   **Response (JSON)**:
    ```json
    {
        "serviceable": true,
        "pincode": "110001",
        "state": "Delhi",
        "district": "New Delhi"
    }
    ```

### Search Autocomplete
*   **Endpoint**: `/api/search-autocomplete`
*   **Method**: `GET`
*   **Parameters**:
    *   `q` (string, Required)
*   **Response (JSON)**: List of matching products containing name, code, slug, and thumbnail.

---

## 2. Shared Location Dropdowns

Used across all panels (Admin, Vendor, DDC, ADC) for dynamic country division selects.

*   **States Lookup**: `GET /api/locations/states`
*   **Districts Lookup**: `GET /api/locations/districts?state_id={id}`
*   **Pincodes Lookup**: `GET /api/locations/pincodes?district_id={id}`

---

## 3. Proxy API Routers

These APIs wrap third-party server endpoints to bypass CORS restriction policies:

### Fetch AMA Codes (Abhhyam India API Proxy)
*   **Route**: `/api/ama-codes`
*   **Controller Method**: `App\Http\Controllers\Controller::getAmaCodes()`
*   **Remote Target**: `https://abhhyamindia.com/api/get_ama_code` (Timeout: 10s)
*   **Format**: JSON list of available AMA coordinator identification codes.

### Running Store Codes
*   **Route**: `/api/running-store-code/{type}`
*   **Method**: `GET`
*   **Purpose**: Resolves incrementing coordinator code values by scanning the latest registration series.

---

## 4. Microservice File Upload Integration

*   **Endpoint URL**: `https://upload.sainikcanteen.com/api/upload`
*   **Method**: `POST`
*   **Payload Type**: `multipart/form-data`
*   **Headers**: None (Parameters are passed in body)
*   **Body Parameters**:
    *   `file`: (Binary File, Max 5MB)
    *   `project_key`: (CHI8M5LQ)
    *   `secret_key`: (q6qWfYt9fYcBj4kjZ4ZtbEl7lg0RjUoD)
    *   `folder_name`: Target subdirectory path string (e.g. `invoices`)
*   **Response (JSON - Success)**:
    ```json
    {
        "status": true,
        "file_url": "https://upload.sainikcanteen.com/uploads/folder_name/filename.png"
    }
    ```
*   **Response (JSON - Validation Fail)**:
    ```json
    {
        "status": false,
        "errors": {
            "file": ["The file size must not exceed 5MB."]
        }
    }
    ```
