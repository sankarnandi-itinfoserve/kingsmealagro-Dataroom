

function showToast(message, type = 'success') {

    let bgColor = type === 'success' ? '#28a745' : '#dc3545';

    let toast = `
        <div class="custom-toast" style="
            background:${bgColor};
            color:#fff;
            padding:12px 18px;
            border-radius:8px;
            margin-bottom:10px;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
            animation:fadeIn 0.3s ease;
        ">
            ${message}
        </div>
    `;

    $('#toastBox').append(toast);

    setTimeout(() => {
        $('#toastBox .custom-toast').first().remove();
    }, 3000);
}