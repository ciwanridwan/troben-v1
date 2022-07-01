export default [
    {
      title: "No Resi",
      key: "code",
      dataIndex: "code",
      align : "center",
      scopedSlots: { customRender: "code" }
    },
    {
      title: "Total Payment",
      key: "total_payment",
      dataIndex: "total_payment",
      align : "center",
      scopedSlots: { customRender: "total_payment" }
    },
    {
      title: "Total Penerimaan",
      key: "total_accepted",
      dataIndex: "total_accepted",
      align : "center", 
      scopedSlots: { customRender: "total_accepted" }
    },
    {
      title: "IsApproved",
      dataIndex: "is_approved",
      key: "is_approved",
      align: "center",
      scopedSlots: { customRender: "is_approved" }
    }
    // {
    //   title: "Status",
    //   key: "action",
    //   align: "center",
    //   width: "100px",
    //   scopedSlots: { customRender: "action" }
    // }
  ];
  