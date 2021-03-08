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
    },
    {
      id: "1",
      barcode: "ORD1234567",
      receiver_name: "Aminah",
      sender_address: "Jl. Ambarawa",
      order_by: "Walk In",
      created_at: "21 Januari 2021"
    },
    {
      id: "1",
      barcode: "ORD1234567",
      receiver_name: "Aminah",
      sender_address: "Jl. Ambarawa",
      order_by: "Walk In",
      created_at: "21 Januari 2021"
    },
    {
      id: "1",
      barcode: "ORD1234567",
      receiver_name: "Aminah",
      sender_address: "Jl. Ambarawa",
      order_by: "aWalk In",
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
const payments = {
  code: "0000",
  error: null,
  message: "success",
  current_page: 1,
  data: [
    {
      id: "1",
      barcode: "ORD1234567",
      partner: {
        code: "MB-JKT-1000"
      },
      price: {
        debit: 123,
        credit: 123
      },
      desc: "Jl. Ambarawa",
      created_at: "21 Januari 2021"
    },
    {
      id: "1",
      barcode: "ORD1234567",
      partner: {
        code: "MB-JKT-1000"
      },
      price: {
        debit: 123,
        credit: 123
      },
      desc: "Jl. Ambarawa",
      created_at: "21 Januari 2021"
    },
    {
      id: "1",
      barcode: "ORD1234567",
      partner: {
        code: "MB-JKT-1000"
      },
      price: {
        debit: 123,
        credit: 123
      },
      desc: "Jl. Ambarawa",
      created_at: "21 Januari 2021"
    }
  ],
  first_page_url: "http://localhost:8000/api/orders?page=1",
  from: 1,
  last_page: 1,
  last_page_url: "http://localhost:8000/api/orders?page=1",
  links: [
    {
      url: null,
      label: "&laquo; Previous",
      active: false
    }
  ],
  next_page_url: "http://localhost:8000/api/orders?page=1",
  path: "http://localhost:8000/api/orders",
  per_page: 15,
  prev_page_url: null,
  to: 3,
  total: 3
};
export { orders, payments };
