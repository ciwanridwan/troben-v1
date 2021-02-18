const orders = {
  code: "0000",
  error: null,
  message: "success",
  current_page: 1,
  data: [
    {
      id: "1",
      barcode: "ORD1234567",
      receiver_name: "Aminah",
      sender_address: "Jl. Ambarawa",
      order_by: "Walk In",
      created_at: "21 Januari 2021"
    }
  ],
  first_page_url: "http://localhost:8000/api/orders?page=1",
  from: 1,
  last_page: 1,
  last_page_url: "http://localhost:8000/api/orders?page=9",
  links: [
    {
      url: null,
      label: "&laquo; Previous",
      active: false
    }
  ],
  next_page_url: "http://localhost:8000/api/orders?page=2",
  path: "http://localhost:8000/api/orders",
  per_page: 15,
  prev_page_url: null,
  to: 15,
  total: 128
};
export { orders };
