panel.plugin("ff3300/panel-logs", {
  components: {
    "k-panel-logs-view": {
      template: `
        <k-panel-inside class="k-panel-logs-view">
          <k-header>
            Logs attività
            <template #buttons>
              <k-button 
                variant="filled" 
                icon="trash" 
                theme="negative"
                @click="clearLogs"
              >
                Clear Logs
              </k-button>
            </template>
          </k-header>

          <k-section>
            <k-box v-if="logs.length === 0" theme="info" text="No logs recorded yet." />
            <k-table 
              v-else
              :columns="columns"
              :rows="rows"
            />
          </k-section>
        </k-panel-inside>
      `,
      props: {
        logs: {
          type: Array,
          default: () => []
        }
      },
      computed: {
        columns() {
          return {
            date: { label: "Date", type: "text", width: "1/6" },
            user: { label: "User", type: "text", width: "1/6" },
            action: { label: "Action", type: "text", width: "1/6" },
            page: { label: "Page", type: "text", width: "1/3" },
            parent: { label: "Parent", type: "text", width: "1/6" }
          };
        },
        rows() {
          return (this.logs || []).map((log, index) => ({
            id: index,
            date: log.date,
            user: log.user,
            action: log.action,
            page: (log.page_title || 'Unknown') + ' (' + (log.page_id || 'None') + ')',
            parent: log.parent_title || 'None'
          }));
        }
      },
      methods: {
        async clearLogs() {
          if (confirm("Are you sure you want to clear all logs?")) {
            try {
              await this.$api.post("panel-logs/clear");
              this.$reload();
              this.$panel.notification.success("Logs cleared successfully.");
            } catch (error) {
              this.$panel.notification.error("Failed to clear logs.");
            }
          }
        }
      }
    }
  }
});
