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
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $this->token,
            'User-Agent: FileManager-App',
            'Accept: application/vnd.github.v3+json',
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }

    public function createRepository($repoName, $description = '') {
        $url = $this->api_url . '/user/repos';
        $data = [
            'name' => $repoName,
            'description' => $description ?: 'File Manager Repository for ' . $repoName,
            'private' => false,
            'auto_init' => true
        ];

        $response = $this->makeRequest($url, 'POST', $data);
        
        if ($response['code'] === 201) {
            // Create initial release
            $this->createRelease($repoName, 'v1.0.0', 'Initial Release');
            return $response['data'];
        }
        return false;
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