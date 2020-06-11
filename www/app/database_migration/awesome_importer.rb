require 'mysql2'

# all tables zed_* must be syncronized before run this script

warehouse = Mysql2::Client.new(host: 'localhost', username: 'root', password: '', database: 'BR_warehouse_full')
natue_wms = Mysql2::Client.new(host: 'localhost', username: 'root', password: '', database: 'development_wms')

# user location (use select ... from user)
user_id = natue_wms.query('SELECT id FROM user where username = "admin"').first['id']
throw RuntimeError, "There is no username called admin. Create one before continue." if user_id.nil?

# operator load (import only user_name not null)
warehouse.query("SELECT * FROM operator where user_name IS NOT NULL", stream: true).each do |operator|
  natue_wms.query("
    INSERT INTO user(
      name, username, username_canonical, email, email_canonical, enabled, salt, password, locked, expired, roles,
      credentials_expired, created_at
    ) VALUES(
      '#{operator['name']}',
      '#{operator['user_name']}',
      '#{operator['user_name']}',
      'ops_#{operator['user_name']}@natue.com.br',
      'ops_#{operator['user_name']}@natue.com.br',
      1,
      '16bkvg6daiw0g40kw4sogkkw8go00c0',
      'oJDwE5rA7rTTPL+EYsU6aCM/DQeU6CJrB2tcisUbj4UD4NFjgeUGH8IpsL4R788gRT88dJcs6+vSGeO0KyPHDA==',
      0,
      0,
      'a:0:{}',
      0,
      NOW()
    )
  ")
end

# zed supplier load - Zed <-> WMS sync required

# zed products load - Zed <-> WMS sync required

# stock_position load
natue_wms.query("DELETE FROM stock_position")
warehouse.query("SELECT * FROM stock_position").each do |position|
  begin
    created_at = DateTime.parse(position['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
    updated_at = DateTime.parse(position['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  rescue
    created_at = DateTime.now.strftime("%Y-%m-%d %H:%M:%S")
    updated_at = DateTime.now.strftime("%Y-%m-%d %H:%M:%S")
  end

  natue_wms.query("
    INSERT INTO stock_position(id, name, sort, pickable, inventory, user, enabled, created_at, updated_at)
    VALUES (
      #{position['id_stock_position']}, '#{position['key']}', #{position['id_stock_position']}, #{position['pickable'].to_i}, #{position['at_inventory'].to_i}, #{user_id}, 1, '#{created_at}', '#{updated_at}'
    )
  ")
end

# packaging
warehouse.query("SELECT * FROM packaging").each do |pack|
  created_at = DateTime.parse(pack['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  updated_at = DateTime.parse(pack['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")

  natue_wms.query("
    INSERT INTO shipping_package(id, name, created_at, updated_at) VALUES(
      #{pack['id_packaging']}, '#{pack['name']}', '#{created_at}', '#{updated_at}'
    )
  ")
end

# logistics_provider - Zed <-> WMS sync required

# shipping_tariff
natue_wms.query("DELETE FROM shipping_tariff")
warehouse.query("SELECT * FROM shipping_tariff").each do |tariff|
  begin
    created_at = DateTime.parse(position['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
    updated_at = DateTime.parse(position['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  rescue
    created_at = DateTime.now.strftime("%Y-%m-%d %H:%M:%S")
    updated_at = DateTime.now.strftime("%Y-%m-%d %H:%M:%S")
  end

  natue_wms.query("INSERT INTO shipping_tariff(id, name, comment, logistics_provider, created_at, updated_at)
    VALUES(
      #{tariff['id_shipping_tariff']}, '#{tariff['name']}', '#{tariff['comment']}', #{tariff['fk_logistics_provider']}, '#{created_at}', '#{updated_at}'
    )
  ")
end

# order_item_status - Zed <-> WMS sync required

# order load - Zed <-> WMS sync required

# order_item_status_history - Zed <-> WMS sync required

# shipping_volume load
warehouse.query("SELECT * FROM shipping_volume").each do |volume|
  # weird case that tracking code is a sql command
  next if volume['tracking_code'].match /^INSERT/i

  created_at = DateTime.parse(volume['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  updated_at = DateTime.parse(volume['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")

  # there a lot of null fk order - wtf
  fk_id_order = volume['fk_id_order'] || 131

  natue_wms.query("
    INSERT INTO shipping_volume(id, tracking_code, zed_order, user, shipping_package, created_at, updated_at)
    VALUES( #{volume['id_shipping_volume']}, '#{volume['tracking_code'].gsub("'", '')}', #{fk_id_order}, #{user_id}, #{volume['fk_id_packaging']}, '#{created_at}', '#{updated_at}'
    )
  ")
end

# incoming_item_status conversion ----- needs to check status conversion
incoming_item_status = {1 => 'incoming', 2 => 'receiving', 3 => 'received'}

# # stock_item status handler (import doesnt have 'returned' status - ignoring from the list below)
stock_item_status = {1 => 'incoming', 2 => 'ready', 3 => 'assigned', 4 => 'waiting_for_picking', 5 => 'picked', 6 => 'sold', 10 => 'damaged', 11 => 'lost', 12 => 'expired'}

warehouse.query('SELECT * FROM incoming_delivery').each do |row|
  next if warehouse.query("
    select COUNT(1) as total from stock_item where fk_id_incoming_item IN (
      select id_incoming_item from incoming_item where fk_id_incoming_delivery = #{row['id_incoming_delivery']}
  )").first['total'] == 0

  incoming_items = warehouse.query("
      SELECT * FROM incoming_item it
      inner join stock_item si ON it.id_incoming_item=si.fk_id_incoming_item
      where it.fk_id_incoming_delivery=#{row['id_incoming_delivery']}
  ")

  product_cost = incoming_items.inject(0) { |sum, n| sum += n['product_cost'].to_i }
  order_date              = DateTime.parse(row['order_date'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  expected_delivery_date  = DateTime.parse(row['expected_delivery_date'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  real_delivery_date      = DateTime.parse(row['real_delivery_date'].to_s).strftime("%Y-%m-%d %H:%M:%S")

  # lets save all data as UTC?
  natue_wms.query("
    INSERT INTO purchase_order(
      invoice_key, date_ordered, date_expected_delivery, date_actual_delivery, volumes_total, volumes_received, cost_total, zed_supplier, user, created_at, updated_at
    ) VALUES(
      '#{row['invoice_key']}', '#{order_date}', '#{expected_delivery_date}', '#{real_delivery_date}', 1, 1, #{product_cost}, #{row['fk_id_supplier']}, #{user_id}, NOW(), NOW()
  );")

  purchase_order_id = natue_wms.query('SELECT LAST_INSERT_ID() as id').first['id']

  incoming_items.each do |item|
    # in some edge cases product_id and incoming_item.fk_id_product may be different. In this cases, consider stock.fk_id_product
    stock_item = warehouse.query("SELECT * FROM stock_item where fk_id_incoming_item=#{item['id_incoming_item']}").first
    product_id = stock_item.fetch('fk_id_product', nil)

    created_at = DateTime.parse(row['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
    updated_at = DateTime.parse(row['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")

    # fix for wrong incoming_status with product in stock.
    actual_incoming_item_status = incoming_item_status[item['fk_id_incoming_item_status']]
    item_status = (product_id and actual_incoming_item_status != 3) ? 3 : actual_incoming_item_status

    natue_wms.query("
      INSERT INTO purchase_order_item(
        cost, created_at, updated_at, purchase_order_item_reception, purchase_order, status, zed_product
      ) VALUES(
        #{item['product_cost'].to_i || 0}, '#{created_at}', '#{updated_at}', NULL, #{purchase_order_id}, #{item_status}, #{product_id}
    );")

    purchase_order_item_id = natue_wms.query("SELECT LAST_INSERT_ID() as id").first['id']

    # stock_item (one <-> one with inventory_item)
    created_at = DateTime.parse(stock_item['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
    updated_at = DateTime.parse(stock_item['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
    date_expiration = DateTime.parse(stock_item['expiration_date'].to_s).strftime("%Y-%m-%d %H:%M:%S")

    # order_item 14411641 nao existe porem tem entrada dele no stock_item oO
    if !stock_item['fk_id_order_item'].nil?
      order_item_exists = natue_wms.query("SELECT COUNT(1) as total FROM zed_order_item where id=#{stock_item['fk_id_order_item']}").first['total']

      if order_item_exists == 0
        p "Notice: zed_order_item n. #{stock_item['fk_id_order_item']} nao existe em stock_item n. #{stock_item['id_stock_item']}, porem ha stock_item que aponta para ele. gnorando a criacao do stock_item."
        stock_item['fk_id_order_item'] = nil
      end
    end

    begin
      natue_wms.query("
        INSERT INTO stock_item(id, date_expiration, barcode, stock_position, zed_order_item, shipping_volume, status, zed_product, purchase_order_item, created_at, updated_at)
        VALUES(
          #{stock_item['id_stock_item']},
          '#{date_expiration}',
          '#{stock_item['barcode']}',
          #{stock_item['fk_id_stock_position'] || 'NULL'},
          #{stock_item['fk_id_order_item'] || 'NULL'},
          #{stock_item['fk_id_shipping_volume'] || 'NULL'},
          '#{stock_item_status[stock_item['fk_id_stock_item_status']]}',
          #{product_id},
          #{purchase_order_item_id},
          '#{created_at}',
          '#{updated_at}'
        )
      ")
    rescue Exception => e
      p e.inspect
      p "fk_id_incoming_item: #{purchase_order_item_id}"
    end
  end
end

# stock_item_status_history (stock_item_status foi mergeada com stock_item.status)
warehouse.query("SELECT * FROM stock_item_status_history", stream: true).each do |history|
  created_at = DateTime.parse(history['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  updated_at = DateTime.parse(history['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")

  total = natue_wms.query("SELECT COUNT(1) as total FROM stock_item where id=#{history['fk_id_stock_item']}").first['total']
  next if total == 0

  natue_wms.query("
    INSERT INTO stock_item_status_history(id, user, stock_item, status, created_at, updated_at) VALUES(
      #{history['id_stock_item_status_history']}, #{user_id}, #{history['fk_id_stock_item']}, '#{stock_item_status[history['fk_id_stock_item_status']]}', '#{created_at}', '#{updated_at}'
    )
  ")
end

# stock_movement_history (stock_item_position_history)
warehouse.query("SELECT * FROM stock_movement_history", stream: true).each do |history|
  created_at = DateTime.parse(history['created_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")
  updated_at = DateTime.parse(history['updated_at'].to_s).strftime("%Y-%m-%d %H:%M:%S")

  total = natue_wms.query("SELECT COUNT(1) as total FROM stock_item where id=#{history['fk_id_stock_item']}").first['total']
  next if total == 0

  natue_wms.query("
    INSERT INTO stock_item_position_history(id, stock_position, stock_item, user, created_at, updated_at) VALUES(
      #{history['id_stock_movements_history']}, #{history['fk_id_stock_position']}, #{history['fk_id_stock_item']}, #{user_id}, '#{created_at}', '#{updated_at}'
    )
  ")
end

# inventory - nao precisa

# nao existe correspondencia direta - product_cost_average_history (eh uma query que o Fernando vai passar para rodar aqui)
cost_avg = warehouse.query("
    SELECT stock_item.fk_id_product, AVG(incoming_item.product_cost) AS value FROM stock_item
    LEFT JOIN incoming_item ON incoming_item.id_incoming_item = stock_item.fk_id_incoming_item
    GROUP BY 1;
    ")
cost_avg.each do |cost|
  natue_wms.query("
    INSERT INTO product_cost_average_history(cost_average, zed_product, created_at, updated_at) VALUES(
      #{cost['value']}, #{cost['fk_id_product']}, NOW(), NOW()
    )
  ")
end
