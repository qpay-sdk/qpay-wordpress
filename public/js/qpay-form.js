document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.qpay-pay-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = btn.closest('.qpay-payment-form');
      var amountInput = form.querySelector('.qpay-amount');
      var amount = amountInput ? amountInput.value : form.dataset.amount;
      var description = form.dataset.description || 'Payment';

      if (!amount || parseFloat(amount) <= 0) { alert('Дүн оруулна уу'); return; }

      btn.disabled = true;
      btn.textContent = 'Уншиж байна...';

      var fd = new FormData();
      fd.append('action', 'qpay_create_invoice');
      fd.append('nonce', qpayAjax.nonce);
      fd.append('amount', amount);
      fd.append('description', description);

      fetch(qpayAjax.url, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) { alert(res.data || 'Error'); btn.disabled = false; btn.textContent = 'QPay-ээр төлөх'; return; }

          var invoice = res.data;
          var result = form.querySelector('.qpay-result');
          form.querySelector('.qpay-form-input').style.display = 'none';
          result.style.display = 'block';

          var html = '<div class="qpay-qr"><img src="data:image/png;base64,' + invoice.qr_image + '" width="256" height="256"></div>';
          html += '<p>Банкны аппликейшнээр төлөх:</p><div class="qpay-banks">';
          (invoice.urls || []).forEach(function (link) {
            var logo = link.logo ? '<img src="' + link.logo + '" width="24" height="24"> ' : '';
            html += '<a href="' + link.link + '" target="_blank" class="qpay-bank-link">' + logo + link.name + '</a>';
          });
          html += '</div><p class="qpay-status">Төлбөр баталгаажихыг хүлээж байна...</p>';
          result.innerHTML = html;

          var poll = setInterval(function () {
            var fd2 = new FormData();
            fd2.append('action', 'qpay_check_payment');
            fd2.append('nonce', qpayAjax.nonce);
            fd2.append('invoice_id', invoice.invoice_id);

            fetch(qpayAjax.url, { method: 'POST', body: fd2 })
              .then(function (r) { return r.json(); })
              .then(function (check) {
                if (check.success && check.data.status === 'paid') {
                  clearInterval(poll);
                  result.innerHTML = '<p class="qpay-success">Төлбөр амжилттай!</p>';
                }
              });
          }, 3000);
        });
    });
  });
});
