import moment from "moment";

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
    title: "Armada",
    dataIndex: "transporter",
    key: "transporter",
    scopedSlots: { customRender: "transporter" }
  },
  {
    title: "Lokasi Penjemputan",
    dataIndex: "sender_address",
    key: "sender_address",
    scopedSlots: { customRender: "sender_address" }
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
        children: moment(text).format('ddd, DD MMM YYYY HH:mm:ss')
      };
    }
  }
];
