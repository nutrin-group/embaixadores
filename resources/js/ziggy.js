const Ziggy = {"url":"http:\/\/embaixadores.test","port":null,"defaults":{},"routes":{"sanctum.csrf-cookie":{"uri":"sanctum\/csrf-cookie","methods":["GET","HEAD"]},"register":{"uri":"register","methods":["GET","HEAD"]},"ambassador.register":{"uri":"register\/ambassador","methods":["GET","HEAD"]},"discounts.check-code":{"uri":"discounts\/check-code\/{code}","methods":["GET","HEAD"],"parameters":["code"]},"login":{"uri":"login","methods":["GET","HEAD"]},"onboarding":{"uri":"onboarding","methods":["GET","HEAD"]},"dashboard":{"uri":"dashboard","methods":["GET","HEAD"]},"posts.index":{"uri":"posts","methods":["GET","HEAD"]},"posts.create":{"uri":"posts\/create","methods":["GET","HEAD"]},"posts.store":{"uri":"posts","methods":["POST"]},"posts.destroy":{"uri":"posts\/{post}","methods":["DELETE"],"parameters":["post"]},"sales":{"uri":"sales","methods":["GET","HEAD"]},"briefings":{"uri":"briefings","methods":["GET","HEAD"]},"commissions":{"uri":"commissions","methods":["GET","HEAD"]},"withdrawals.index":{"uri":"withdrawals","methods":["GET","HEAD"]},"withdrawals.store":{"uri":"withdrawals","methods":["POST"]},"links":{"uri":"links","methods":["GET","HEAD"]},"manager.dashboard":{"uri":"manager\/dashboard","methods":["GET","HEAD"]},"manager.team":{"uri":"manager\/team","methods":["GET","HEAD"]},"manager.sales":{"uri":"manager\/sales","methods":["GET","HEAD"]},"manager.commissions":{"uri":"manager\/commissions","methods":["GET","HEAD"]},"admin.dashboard":{"uri":"admin\/dashboard","methods":["GET","HEAD"]},"admin.statistics":{"uri":"admin\/statistics","methods":["GET","HEAD"]},"admin.managers.index":{"uri":"admin\/managers","methods":["GET","HEAD"]},"admin.managers.create":{"uri":"admin\/managers\/create","methods":["GET","HEAD"]},"admin.managers.store":{"uri":"admin\/managers","methods":["POST"]},"admin.managers.show":{"uri":"admin\/managers\/{manager}","methods":["GET","HEAD"],"parameters":["manager"]},"admin.managers.edit":{"uri":"admin\/managers\/{manager}\/edit","methods":["GET","HEAD"],"parameters":["manager"],"bindings":{"manager":"id"}},"admin.managers.update":{"uri":"admin\/managers\/{manager}","methods":["PUT","PATCH"],"parameters":["manager"],"bindings":{"manager":"id"}},"admin.managers.destroy":{"uri":"admin\/managers\/{manager}","methods":["DELETE"],"parameters":["manager"],"bindings":{"manager":"id"}},"admin.ambassadors":{"uri":"admin\/ambassadors","methods":["GET","HEAD"]},"admin.commissions":{"uri":"admin\/commissions","methods":["GET","HEAD"]},"admin.reports":{"uri":"admin\/reports","methods":["GET","HEAD"]},"admin.settings":{"uri":"admin\/settings","methods":["GET","HEAD"]},"admin.withdrawals.index":{"uri":"admin\/withdrawals","methods":["GET","HEAD"]},"admin.withdrawals.approve":{"uri":"admin\/withdrawals\/{id}\/approve","methods":["POST"],"parameters":["id"]},"admin.withdrawals.cancel":{"uri":"admin\/withdrawals\/{id}\/cancel","methods":["POST"],"parameters":["id"]},"profile.edit":{"uri":"profile","methods":["GET","HEAD"]},"profile.update":{"uri":"profile","methods":["PATCH"]},"profile.destroy":{"uri":"profile","methods":["DELETE"]},"discounts.create":{"uri":"discounts","methods":["POST"]},"shopify.orders.new":{"uri":"shopify\/orders\/new","methods":["POST"]},"shopify.orders.process":{"uri":"shopify\/orders\/process","methods":["POST"]},"shopify.products.get-image":{"uri":"shopify\/products\/get-image","methods":["POST"]},"password.request":{"uri":"forgot-password","methods":["GET","HEAD"]},"password.email":{"uri":"forgot-password","methods":["POST"]},"password.reset":{"uri":"reset-password\/{token}","methods":["GET","HEAD"],"parameters":["token"]},"password.store":{"uri":"reset-password","methods":["POST"]},"verification.notice":{"uri":"verify-email","methods":["GET","HEAD"]},"verification.verify":{"uri":"verify-email\/{id}\/{hash}","methods":["GET","HEAD"],"parameters":["id","hash"]},"verification.send":{"uri":"email\/verification-notification","methods":["POST"]},"password.confirm":{"uri":"confirm-password","methods":["GET","HEAD"]},"password.update":{"uri":"password","methods":["PUT"]},"logout":{"uri":"logout","methods":["POST"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
