export default [
  {
    title: "No",
    dataIndex: "number"
    // customRender: (text, row, index) => {
    //   return {
    //     children: text,
    //     attrs: {
    //       rowSpan: 2
    //     }
    //   };
    // }
  },
  {
    title: "ID Order",
    dataIndex: "barcode",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    key: "created_at",
    width: "100px",
    colSpan: 2,
    align: "center",
    customRender: (text, row, index) => {
      return {
        attrs: {
          colSpan: 2
        },
        children: text
      };
    }
  }
];
