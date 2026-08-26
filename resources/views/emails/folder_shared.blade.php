<h2>Folder Shared With You</h2>

<p>A folder has been shared with you.</p>

<p><strong>Folder Name:</strong> {{ $folder->name }}</p>
<p><strong>Permission:</strong> {{ ucfirst($permission) }}</p>

<p>
    Click below to access:
</p>

<a href="{{ $link }}" style="padding:10px 15px;background:#007bff;color:#fff;text-decoration:none;">
    Open Folder
</a>

<p>If you don’t have access, please contact the sender.</p>