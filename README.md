# QPay Payment Forms for WordPress

[![CI](https://github.com/qpay-sdk/qpay-wordpress/actions/workflows/ci.yml/badge.svg)](https://github.com/qpay-sdk/qpay-wordpress/actions)

QPay V2 payment forms plugin for WordPress (no WooCommerce required).

## Install

1. Upload `qpay-wordpress` folder to `/wp-content/plugins/`
2. Activate in WordPress
3. Go to Settings > QPay to configure

## Usage

### Shortcode

```
[qpay_payment_form amount="10000" description="Membership fee"]
```

Variable amount (user enters):
```
[qpay_payment_form description="Donation"]
```

### Features

- Shortcode-based payment forms
- QR code + bank app deeplinks
- Auto payment polling
- Payment tracking dashboard (QPay Payments menu)
- REST API webhook endpoint

## License

MIT
