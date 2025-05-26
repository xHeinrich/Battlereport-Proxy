This document describes a PHP application designed to act as a proxy service for fetching and serving Battlefield 4 (BF4) battlereport data. It retrieves data from the official `battlelog.battlefield.com` servers and makes it available to other applications or services. This allows developers to access BF4 battlereport data without directly querying the official servers, potentially reducing load on those servers and providing a more stable and reliable data source.

## Input

The application expects HTTP `POST` requests. The request body must be JSON.

The input JSON should be an object with a key `reports`. The `reports` key should contain an array of objects, where each object has the following structure:

*   `report_id` (integer): The ID of the battle report.
*   `player_id` (integer): The ID of the player.
*   `all` (boolean, optional): If true, the application will attempt to fetch reports for all players present in the initial report specified by `report_id`.

Example:
```json
{
  "reports": [
    {
      "report_id": 1234567890,
      "player_id": 100001
    },
    {
      "report_id": 9876543210,
      "player_id": 100002,
      "all": true
    }
  ]
}
```

## Output

The application outputs a JSON array containing the processed battlereport data. The specific structure of the output data will depend on the data retrieved from `battlelog.battlefield.com`.

## Components

### `index.php`

*   **Role:** Main entry point of the application.
*   **Responsibilities:**
    *   Handles incoming HTTP POST requests.
    *   Validates the user's IP against a whitelist.
    *   Decodes the JSON input.
    *   Orchestrates the fetching of battlereports using `BattlereportClient`.
    *   Processes reports using the `Battlereport` class.
    *   Returns the aggregated data as a JSON response.

### `Battlereport.php`

*   **Role:** Defines a data structure for a battlereport.
*   **Responsibilities:**
    *   Stores player-specific report data, team information, and game server details.
    *   Includes a method `getAllPlayerIdsInReport()` to extract all unique player IDs participating in the match from the team data.

### `BattlereportClient.php`

*   **Role:** Manages HTTP communication with the external Battlelog API.
*   **Responsibilities:**
    *   Configures and provides a Guzzle HTTP client instance.
    *   Implements retry logic for requests (using `caseyamcl/guzzle_retry_middleware`) to handle transient network issues.
    *   Sets custom HTTP headers for requests to the Battlelog API, such as `User-Agent` and `X-AjaxNavigation`.

### `composer.json`

*   **Role:** PHP project configuration file for managing dependencies.
*   **Responsibilities:**
    *   Lists project dependencies, specifically `guzzlehttp/guzzle` (for making HTTP requests) and `caseyamcl/guzzle_retry_middleware` (for adding retry capabilities to Guzzle requests).

## Key Features

*   **Asynchronous Operations**: Utilizes Guzzle's asynchronous capabilities to fetch multiple battlereports concurrently, improving efficiency when multiple reports are requested.
*   **Comprehensive Report Fetching**: Offers an option (`all: true` in the input) to fetch not only the specified player's report but also the reports for every other player who participated in the same game.
*   **Resilient HTTP Requests**: Implements an automatic retry mechanism for requests to the external Battlelog API, making the application more robust against temporary network failures or server issues.
*   **IP-Based Access Control**: Includes a basic security measure by restricting access to the service to a predefined list of allowed IP addresses.

This document provides a comprehensive overview of the BF4 Battlereport Proxy application, its functionalities, components, and key features.
