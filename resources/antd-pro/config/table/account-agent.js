export default [
    {
      title: "No",
      key: "number",
      dataIndex: "number",
      scopedSlots: { customRender: "number" }
    },
    {
      title: "Nama",
      dataIndex: "name",
      key: "name",
      scopedSlots: { customRender: "name" }
    },
    {
      title: "Email",
      dataIndex: "email",
      key: "email",
      scopedSlots: { customRender: "email" }
    },
    {
      title: "Status",
      key: "status",
      width: "180px",
      scopedSlots: { customRender: "status" }
    }
  ];
  