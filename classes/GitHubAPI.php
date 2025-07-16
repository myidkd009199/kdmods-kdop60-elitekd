<?php
class GitHubAPI {
    private $token;
    private $username;
    private $api_url;

    public function __construct($token, $username) {
        $this->token = $token;
        $this->username = $username;
        $this->api_url = GITHUB_API_URL;
    }

    private function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'User-Agent: FileManager-App/1.0',
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        // Log errors for debugging
        if ($httpCode >= 400 || $curlError) {
            error_log("GitHub API Error - URL: $url, Method: $method, HTTP Code: $httpCode, cURL Error: $curlError, Response: " . $response);
        }

        return [
            'code' => $httpCode,
            'data' => $responseData,
            'curl_error' => $curlError,
            'raw_response' => $response
        ];
    }

    public function createRepository($repoName, $description = '') {
        $url = $this->api_url . '/user/repos';
        $data = [
            'name' => $repoName,
            'description' => $description ?: 'File Manager Repository for ' . $repoName,
            'private' => false,
            'auto_init' => true,
            'has_issues' => true,
            'has_projects' => false,
            'has_wiki' => false
        ];

        $response = $this->makeRequest($url, 'POST', $data);
        
        // Log the response for debugging
        error_log("GitHub Create Repo Response - Code: " . $response['code'] . ", Data: " . json_encode($response['data']));
        
        if ($response['code'] === 201 && isset($response['data']['html_url'])) {
            // Create initial release (with delay to ensure repo is ready)
            sleep(2);
            $release_response = $this->createRelease($repoName, 'v1.0.0', 'Initial Release');
            
            // Return repository data even if release fails
            return $response['data'];
        }
        
        // Return detailed error information
        return [
            'error' => true,
            'code' => $response['code'],
            'message' => $response['data']['message'] ?? 'Unknown error',
            'curl_error' => $response['curl_error'] ?? null,
            'data' => $response['data']
        ];
    }

    public function createRelease($repoName, $tagName, $releaseName, $description = '') {
        $url = $this->api_url . '/repos/' . $this->username . '/' . $repoName . '/releases';
        $data = [
            'tag_name' => $tagName,
            'name' => $releaseName,
            'body' => $description ?: 'Automatic release for file manager',
            'draft' => false,
            'prerelease' => false
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    public function deleteRepository($repoName) {
        $url = $this->api_url . '/repos/' . $this->username . '/' . $repoName;
        return $this->makeRequest($url, 'DELETE');
    }

    public function uploadFile($repoName, $filePath, $content, $commitMessage = '') {
        $url = $this->api_url . '/repos/' . $this->username . '/' . $repoName . '/contents/' . $filePath;
        $data = [
            'message' => $commitMessage ?: 'Upload file: ' . $filePath,
            'content' => base64_encode($content)
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    public function deleteFile($repoName, $filePath, $sha, $commitMessage = '') {
        $url = $this->api_url . '/repos/' . $this->username . '/' . $repoName . '/contents/' . $filePath;
        $data = [
            'message' => $commitMessage ?: 'Delete file: ' . $filePath,
            'sha' => $sha
        ];

        return $this->makeRequest($url, 'DELETE', $data);
    }

    public function getFileContent($repoName, $filePath) {
        $url = $this->api_url . '/repos/' . $this->username . '/' . $repoName . '/contents/' . $filePath;
        return $this->makeRequest($url);
    }

    public function getRepositoryFiles($repoName, $path = '') {
        $url = $this->api_url . '/repos/' . $this->username . '/' . $repoName . '/contents/' . $path;
        return $this->makeRequest($url);
    }
}
?>