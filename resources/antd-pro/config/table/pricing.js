export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Dari & Ke",
    key: "from_to",
    scopedSlots: { customRender: "from_to" }
  },
  {
    title: "Tarif 0 - 10 Kg",
    dataIndex: "tier_1",
    key: "tier_1",
    scopedSlots: { customRender: "tier_1" }
  },
  {
    title: "Tarif 11 - 30 Kg",
    dataIndex: "tier_2",
    key: "tier_2",
    scopedSlots: { customRender: "tier_2" }
  },
  {
    title: "Tarif 31 - 50 Kg",
    dataIndex: "tier_3",
    key: "tier_3",
    scopedSlots: { customRender: "tier_3" }
  },
  {
    title: "Tarif 51 - 100 Kg",
    dataIndex: "tier_4",
    key: "tier_4",
    scopedSlots: { customRender: "tier_4" }
  },
  {
    title: "Tarif 101 - 1.000 Kg",
    dataIndex: "tier_5",
    key: "tier_5",
    scopedSlots: { customRender: "tier_5" }
  },
  {
    title: "Tarif > 1.000 Kg",
    dataIndex: "tier_6",
    key: "tier_6",
    scopedSlots: { customRender: "tier_6" }
  },
  {
    title: "Jenis",
    dataIndex: "service.name",
    key: "service",
    scopedSlots: { customRender: "service" }
  },
  {
    title: "Catatan",
    dataIndex: "zip_code",
    key: "zip_code",
    scopedSlots: { customRender: "zip_code" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];